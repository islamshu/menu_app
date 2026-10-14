<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Http\Requests\Restaurant\ExtractPaletteRequest;
use App\Http\Requests\Restaurant\UpdateBrandingRequest;
use App\Models\Restaurant;
use App\Models\RestaurantBranding;
use App\Services\Branding\BrandingSettings;
use App\Services\Branding\PaletteExtractor;
use App\Services\ImageManager;
use App\Services\MenuContentPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BrandingController extends Controller
{
    use ResolvesActiveRestaurant;

    public function __construct(
        private readonly BrandingSettings $settings,
        private readonly PaletteExtractor $palette,
        private readonly MenuContentPresenter $content,
        private readonly ImageManager $images,
    ) {}

    /**
     * Show the branding studio (owner/manager only).
     */
    public function edit(): Response
    {
        $restaurant = $this->restaurant();
        Gate::authorize('update', $restaurant);

        $content = $this->content->present($restaurant);

        return Inertia::render('Restaurant/Branding/Studio', [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'logo' => $this->images->url($restaurant->logo),
                'cover_image' => $this->images->url($restaurant->cover_image),
                'currency' => $restaurant->currency,
                'phone' => $restaurant->phone,
                'email' => $restaurant->email,
                'address' => $restaurant->address,
                'locale' => $restaurant->default_locale ?: config('locales.default'),
            ],
            'locales' => collect(config('locales.supported'))
                ->map(fn (array $config, string $code) => [
                    'code' => $code,
                    'label' => $config['label'],
                    'dir' => $config['dir'],
                ])
                ->values(),
            'themes' => $this->settings->themesForFrontend(),
            'options' => $this->settings->options(),
            'published' => $this->settings->resolve($restaurant),
            'draft' => $this->settings->draft($restaurant),
            'has_draft' => $this->settings->hasDraft($restaurant),
            'menu' => [
                'categories' => $content['categories'],
                'featured' => $content['featured'],
            ],
            'menu_url' => route('menu.show', $restaurant->slug),
        ]);
    }

    /**
     * Save the current studio values as an unpublished draft.
     */
    public function update(UpdateBrandingRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $settings = $this->settings->normalize($request->validated());

        $branding = $this->brandingFor($restaurant);
        $branding->draft = $settings;
        $branding->save();

        return back()->with('success', 'تم حفظ تغييرات الهوية كمسودة.');
    }

    /**
     * Publish the current studio values to the live public menu.
     */
    public function publish(UpdateBrandingRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $settings = $this->settings->normalize($request->validated());

        $branding = $this->brandingFor($restaurant);
        $branding->published = $settings;
        $branding->draft = null;
        $branding->save();

        return back()->with('success', 'تم نشر الهوية على القائمة الرقمية.');
    }

    /**
     * Discard the working draft and revert to the published settings.
     */
    public function discardDraft(): RedirectResponse
    {
        $restaurant = $this->restaurant();

        $branding = $restaurant->branding;

        if ($branding) {
            $branding->draft = null;
            $branding->save();
        }

        return back()->with('success', 'تم تجاهل المسودة والعودة إلى الهوية المنشورة.');
    }

    /**
     * Analyse the restaurant logo (or an uploaded one) and suggest a palette.
     */
    public function extractPalette(ExtractPaletteRequest $request): JsonResponse
    {
        $restaurant = $this->restaurant();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->getRealPath();
        } else {
            $path = $restaurant->logo
                ? Storage::disk(ImageManager::DISK)->path($restaurant->logo)
                : null;
        }

        if (! $path || ! is_file($path)) {
            return response()->json([
                'available' => false,
                'message' => 'لم يتم العثور على شعار لتحليله. يرجى رفع شعار أولاً.',
            ], 422);
        }

        $palette = $this->palette->extract($path);

        if ($palette === null) {
            return response()->json([
                'available' => false,
                'message' => 'تعذّر تحليل الشعار تلقائياً. يمكنك اختيار الألوان يدوياً.',
            ]);
        }

        return response()->json([
            'available' => true,
            'palette' => $palette,
        ]);
    }

    /**
     * The branding row for a restaurant, or a fresh unsaved instance.
     */
    protected function brandingFor(Restaurant $restaurant): RestaurantBranding
    {
        $branding = $restaurant->branding()->first();

        if (! $branding) {
            $branding = new RestaurantBranding;
            $branding->restaurant_id = $restaurant->id;
        }

        return $branding;
    }
}
