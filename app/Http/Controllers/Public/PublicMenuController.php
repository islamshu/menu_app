<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\ImageManager;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicMenuController extends Controller
{
    public function __construct(private readonly ImageManager $images) {}

    /**
     * Display the public digital menu for an active restaurant.
     *
     * The menu is read-only. Only active restaurants, active categories and
     * available items are exposed, and every record is scoped to the
     * restaurant resolved from the route, so cross-tenant data can never leak.
     */
    public function __invoke(Restaurant $restaurant): Response
    {
        abort_unless($restaurant->isActive(), 404);

        $locale = $this->resolveLocale($restaurant);

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

        $featured = $categories
            ->flatMap(fn (MenuCategory $category) => $category->items)
            ->filter(fn (MenuItem $item) => $item->is_featured)
            ->take(6)
            ->values();

        return Inertia::render('Public/Menu', [
            'restaurant' => $this->restaurantPayload($restaurant, $locale),
            'categories' => $categories
                ->map(fn (MenuCategory $category) => $this->categoryPayload($category))
                ->values(),
            'featured' => $featured
                ->map(fn (MenuItem $item) => $this->itemPayload($item))
                ->values(),
            'meta' => $this->meta($restaurant, $locale),
        ]);
    }

    /**
     * The restaurant's default locale when supported, otherwise the app default.
     */
    protected function resolveLocale(Restaurant $restaurant): string
    {
        $supported = array_keys(config('locales.supported'));

        if ($restaurant->default_locale && in_array($restaurant->default_locale, $supported, true)) {
            return $restaurant->default_locale;
        }

        return config('locales.default');
    }

    /**
     * @return array<string, mixed>
     */
    protected function restaurantPayload(Restaurant $restaurant, string $locale): array
    {
        return [
            'name' => $restaurant->name,
            'slug' => $restaurant->slug,
            'description' => $restaurant->description,
            'logo' => $this->images->url($restaurant->logo),
            'cover_image' => $this->images->url($restaurant->cover_image),
            'phone' => $restaurant->phone,
            'email' => $restaurant->email,
            'address' => $restaurant->address,
            'currency' => $restaurant->currency,
            'locale' => $locale,
            'dir' => config("locales.supported.{$locale}.dir", 'ltr'),
            'locales' => collect(config('locales.supported'))
                ->map(fn (array $config, string $code) => [
                    'code' => $code,
                    'label' => $config['label'],
                    'dir' => $config['dir'],
                ])
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function categoryPayload(MenuCategory $category): array
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
     * @return array<string, mixed>
     */
    protected function itemPayload(MenuItem $item): array
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

    /**
     * SEO payload used by the Inertia <Head> component.
     *
     * @return array<string, string|null>
     */
    protected function meta(Restaurant $restaurant, string $locale): array
    {
        $description = trim(strip_tags((string) $restaurant->description));

        if ($description === '') {
            $description = $locale === 'ar'
                ? "تصفّح قائمة {$restaurant->name} الرقمية."
                : "Browse the digital menu of {$restaurant->name}.";
        }

        $ogImage = $restaurant->cover_image ?: $restaurant->logo;

        return [
            'title' => $restaurant->name,
            'description' => Str::limit($description, 155),
            'canonical' => route('menu.show', $restaurant->slug),
            'image' => $ogImage ? url($this->images->url($ogImage)) : null,
            'locale' => $locale,
            'type' => 'website',
        ];
    }
}
