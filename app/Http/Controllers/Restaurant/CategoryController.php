<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Http\Requests\Restaurant\ReorderRequest;
use App\Http\Requests\Restaurant\StoreCategoryRequest;
use App\Http\Requests\Restaurant\ToggleRequest;
use App\Http\Requests\Restaurant\UpdateCategoryRequest;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use App\Services\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use ResolvesActiveRestaurant;

    public function __construct(private readonly ImageManager $images) {}

    /**
     * List the categories of the active restaurant.
     */
    public function index(Request $request): Response
    {
        $restaurant = $this->restaurant();
        $search = trim((string) $request->query('search', ''));

        $categories = $restaurant->menuCategories()
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (MenuCategory $category) => $this->present($category));

        return Inertia::render('Restaurant/Categories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $data = $request->validated();

        $restaurant->menuCategories()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image' => $request->hasFile('image')
                ? $this->images->store($request->file('image'), $this->directory($restaurant))
                : null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order']
                ?? (($restaurant->menuCategories()->max('sort_order') ?? 0) + 1),
        ]);

        return back()->with('success', 'تم إنشاء القسم بنجاح.');
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, MenuCategory $category): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $this->ensureOwned($restaurant, $category);

        $data = $request->validated();
        $directory = $this->directory($restaurant);

        if ($request->hasFile('image')) {
            $category->image = $this->images->store(
                $request->file('image'),
                $directory,
                $category->image,
            );
        } elseif ($request->boolean('remove_image')) {
            $this->images->delete($category->image, $directory);
            $category->image = null;
        }

        $category->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', $category->is_active),
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ])->save();

        return back()->with('success', 'تم تحديث القسم بنجاح.');
    }

    /**
     * Delete a category, detaching its items instead of deleting them.
     */
    public function destroy(MenuCategory $category): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $this->ensureOwned($restaurant, $category);

        $category->items()->update(['menu_category_id' => null]);

        $this->images->delete($category->image, $this->directory($restaurant));

        $category->delete();

        return back()->with('success', 'تم حذف القسم. الأصناف المرتبطة أصبحت غير مصنّفة.');
    }

    /**
     * Toggle a boolean flag on the category.
     */
    public function toggle(ToggleRequest $request, MenuCategory $category): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $this->ensureOwned($restaurant, $category);

        $field = $request->validated('field');
        abort_unless(in_array($field, ['is_active'], true), 422);

        $category->update([$field => $request->boolean('value')]);

        return back();
    }

    /**
     * Persist a new manual ordering.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $ids = $request->validated('items');

        $categories = $restaurant->menuCategories()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach (array_values($ids) as $index => $id) {
            $categories->get($id)?->update(['sort_order' => $index]);
        }

        return back()->with('success', 'تم تحديث ترتيب الأقسام.');
    }

    protected function ensureOwned(Restaurant $restaurant, MenuCategory $category): void
    {
        abort_unless($category->restaurant_id === $restaurant->id, 404);
    }

    protected function directory(Restaurant $restaurant): string
    {
        return $this->images->directory($restaurant->id, 'categories');
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(MenuCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'slug' => $category->slug,
            'image' => $this->images->url($category->image),
            'is_active' => $category->is_active,
            'sort_order' => $category->sort_order,
            'items_count' => $category->items_count ?? null,
        ];
    }
}
