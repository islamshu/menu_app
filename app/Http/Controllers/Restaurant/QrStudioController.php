<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Http\Requests\Restaurant\UpdateQrCodeRequest;
use App\Models\Restaurant;
use App\Models\RestaurantQrCode;
use App\Services\ImageManager;
use App\Services\Qr\QrSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QrStudioController extends Controller
{
    use ResolvesActiveRestaurant;

    public function __construct(
        private readonly QrSettings $settings,
        private readonly ImageManager $images,
    ) {}

    public function edit(): Response
    {
        $restaurant = $this->restaurant();
        Gate::authorize('update', $restaurant);

        return Inertia::render('Restaurant/Qr/Studio', [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'slug' => $restaurant->slug,
                'logo' => $this->images->url($restaurant->logo),
            ],
            'menu_url' => route('menu.show', $restaurant->slug),
            'presets' => $this->settings->presetsForFrontend(),
            'options' => $this->settings->options(),
            'published' => $this->settings->resolve($restaurant),
            'draft' => $this->settings->draft($restaurant),
            'has_draft' => $this->settings->hasDraft($restaurant),
        ]);
    }

    public function update(UpdateQrCodeRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $settings = $this->settings->normalize($request->validated());

        $qr = $this->qrFor($restaurant);
        $qr->draft = $settings;
        $qr->save();

        return back()->with('success', 'تم حفظ إعدادات الرمز كمسودة.');
    }

    public function publish(UpdateQrCodeRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $settings = $this->settings->normalize($request->validated());

        $qr = $this->qrFor($restaurant);
        $qr->published = $settings;
        $qr->draft = null;
        $qr->save();

        return back()->with('success', 'تم نشر إعدادات الرمز على القائمة الرقمية.');
    }

    public function discardDraft(): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $qr = $restaurant->qrCode;

        if ($qr) {
            $qr->draft = null;
            $qr->save();
        }

        return back()->with('success', 'تم تجاهل المسودة والعودة إلى الإعدادات المنشورة.');
    }

    protected function qrFor(Restaurant $restaurant): RestaurantQrCode
    {
        $qr = $restaurant->qrCode()->first();

        if (! $qr) {
            $qr = new RestaurantQrCode;
            $qr->restaurant_id = $restaurant->id;
        }

        return $qr;
    }
}
