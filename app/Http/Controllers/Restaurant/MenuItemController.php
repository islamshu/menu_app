<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Http\Requests\Restaurant\ReorderRequest;
use App\Http\Requests\Restaurant\StoreMenuItemRequest;
use App\Http\Requests\Restaurant\ToggleRequest;
use App\Http\Requests\Restaurant\UpdateMenuItemRequest;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuItemController extends Controller
{
    use ResolvesActiveRestaurant;

    public function __construct(private readonly ImageManager $images) {}

    /**
     * List the menu items of the active restaurant with filters.
     */
    public function index(Request $request): Response
    {
        $restaurant = $this->restaurant();

        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');
        $availability = $request->query('availability');

        $items = $restaurant->menuItems()
            ->with('category:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            })
            ->when(filled($categoryId), fn ($query) => $query->where('menu_category_id', (int) $categoryId))
            ->when($availability === 'available', fn ($query) => $query->where('is_available', true))
            ->when($availability === 'unavailable', fn ($query) => $query->where('is_available', false))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (MenuItem $item) => $this->present($item));

        $categories = $restaurant->menuCategories()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name]);

        return Inertia::render('Restaurant/Items/Index', [
            'items' => $items,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'availability' => $availability,
            ],
        ]);
    }

    /**
     * Store a new menu item.
     */
    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $data = $request->validated();

        $restaurant->menuItems()->create([
            'menu_category_id' => $this->resolveCategory($restaurant, $data['menu_category_id'] ?? null),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'image' => $request->hasFile('image')
                ? $this->images->store($request->file('image'), $this->directory($restaurant))
                : null,
            'is_available' => $request->boolean('is_available', true),
            'is_featured' => $request->boolean('is_featured', false),
            'is_popular' => $request->boolean('is_popular', false),
            'is_new' => $request->boolean('is_new', false),
            'sort_order' => $data['sort_order']
                ?? (($restaurant->menuItems()->max('sort_order') ?? 0) + 1),
        ]);

        return back()->with('success', 'تم إنشاء الصنف بنجاح.');
    }

    /**
     * Update an existing menu item.
     */
    public function update(UpdateMenuItemRequest $request, MenuItem $item): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $this->ensureOwned($restaurant, $item);

        $data = $request->validated();
        $directory = $this->directory($restaurant);

        if ($request->hasFile('image')) {
            $item->image = $this->images->store(
                $request->file('image'),
                $directory,
                $item->image,
            );
        } elseif ($request->boolean('remove_image')) {
            $this->images->delete($item->image, $directory);
            $item->image = null;
        }

        $item->fill([
            'menu_category_id' => $this->resolveCategory($restaurant, $data['menu_category_id'] ?? null),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'is_available' => $request->boolean('is_available', $item->is_available),
            'is_featured' => $request->boolean('is_featured', $item->is_featured),
            'is_popular' => $request->boolean('is_popular', $item->is_popular),
            'is_new' => $request->boolean('is_new', $item->is_new),
            'sort_order' => $data['sort_order'] ?? $item->sort_order,
        ])->save();

        return back()->with('success', 'تم تحديث الصنف بنجاح.');
    }

    /**
     * Delete a menu item and its image.
     */
    public function destroy(MenuItem $item): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $this->ensureOwned($restaurant, $item);

        $this->images->delete($item->image, $this->directory($restaurant));

        $item->delete();

        return back()->with('success', 'تم حذف الصنف.');
    }

    /**
     * Toggle a boolean flag on the item.
     */
    public function toggle(ToggleRequest $request, MenuItem $item): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $this->ensureOwned($restaurant, $item);

        $field = $request->validated('field');
        abort_unless(
            in_array($field, ['is_available', 'is_featured', 'is_popular', 'is_new'], true),
            422,
        );

        $item->update([$field => $request->boolean('value')]);

        return back();
    }

    /**
     * Persist a new manual ordering.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $ids = $request->validated('items');

        $items = $restaurant->menuItems()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach (array_values($ids) as $index => $id) {
            $items->get($id)?->update(['sort_order' => $index]);
        }

        return back()->with('success', 'تم تحديث ترتيب الأصناف.');
    }

    /**
     * Ensure a submitted category belongs to the active restaurant.
     */
    protected function resolveCategory(Restaurant $restaurant, mixed $categoryId): ?int
    {
        if (blank($categoryId)) {
            return null;
        }

        $owned = $restaurant->menuCategories()->whereKey($categoryId)->exists();

        abort_unless($owned, 422, 'القسم المحدد غير صالح.');

        return (int) $categoryId;
    }

    protected function ensureOwned(Restaurant $restaurant, MenuItem $item): void
    {
        abort_unless($item->restaurant_id === $restaurant->id, 404);
    }

    protected function directory(Restaurant $restaurant): string
    {
        return $this->images->directory($restaurant->id, 'items');
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'slug' => $item->slug,
            'image' => $this->images->url($item->image),
            'price' => $item->price,
            'compare_at_price' => $item->compare_at_price,
            'menu_category_id' => $item->menu_category_id,
            'category_name' => $item->category?->name,
            'is_available' => $item->is_available,
            'is_featured' => $item->is_featured,
            'is_popular' => $item->is_popular,
            'is_new' => $item->is_new,
            'sort_order' => $item->sort_order,
        ];
    }
}
