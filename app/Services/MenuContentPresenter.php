<?php

namespace App\Services;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Support\Collection;

/**
 * Builds the read-only, tenant-scoped menu payload shared by the public menu
 * and the branding studio preview, so neither surface duplicates menu queries.
 */
class MenuContentPresenter
{
    public function __construct(private readonly ImageManager $images) {}

    /**
     * @return array{categories: Collection<int, array<string, mixed>>, featured: Collection<int, array<string, mixed>>}
     */
    public function present(Restaurant $restaurant): array
    {
        $categories = $restaurant->menuCategories()
            ->where('is_active', true)
            ->whereHas('items', fn ($query) => $query->where('is_available', true))
            ->with(['items' => fn ($query) => $query
                ->where('is_available', true)
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $items = $categories->flatMap(fn (MenuCategory $category) => $category->items);

        $featured = $items
            ->filter(fn (MenuItem $item) => $item->is_featured)
            ->take(6)
            ->values();

        // The "Most Popular" virtual category is derived from the already
        // loaded, tenant-scoped, active/available set, so it never triggers
        // extra queries and can not leak other restaurants' or hidden items.
        $popular = $items
            ->filter(fn (MenuItem $item) => $item->is_popular)
            ->values();

        $payload = $categories
            ->map(fn (MenuCategory $category) => $this->categoryPayload($category));

        if ($popular->isNotEmpty()) {
            $payload->prepend($this->popularCategoryPayload($popular));
        }

        return [
            'categories' => $payload->values(),
            'featured' => $featured
                ->map(fn (MenuItem $item) => $this->itemPayload($item))
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function categoryPayload(MenuCategory $category): array
    {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $category->translationsFor('name'),
            'description' => $category->translationsFor('description'),
            'image' => $this->images->url($category->image),
            'items' => $category->items
                ->map(fn (MenuItem $item) => $this->itemPayload($item))
                ->values(),
        ];
    }

    /**
     * Build the virtual "Most Popular" category that always renders first.
     *
     * @param  Collection<int, MenuItem>  $items
     * @return array<string, mixed>
     */
    public function popularCategoryPayload(Collection $items): array
    {
        return [
            'id' => 'most-popular',
            'slug' => 'most-popular',
            'virtual' => true,
            'name' => [
                'ar' => 'الأكثر طلباً',
                'en' => 'Most Popular',
            ],
            'description' => [
                'ar' => 'أطباق مختارة من قائمتنا يكثر طلبها بين ضيوفنا.',
                'en' => 'A hand-picked selection of our most in-demand dishes.',
            ],
            'image' => null,
            'items' => $items
                ->map(fn (MenuItem $item) => $this->itemPayload($item))
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function itemPayload(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'name' => $item->translationsFor('name'),
            'description' => $item->translationsFor('description'),
            'image' => $this->images->url($item->image),
            'price' => $item->price,
            'compare_at_price' => $item->compare_at_price,
            'is_available' => (bool) $item->is_available,
            'is_featured' => (bool) $item->is_featured,
            'is_popular' => (bool) $item->is_popular,
            'is_new' => (bool) $item->is_new,
            'category_id' => $item->menu_category_id,
        ];
    }
}
