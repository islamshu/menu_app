<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Http\Requests\Restaurant\UpdateQrCodeRequest;
use App\Models\Restaurant;
use App\Models\RestaurantQrCode;
use App\Services\ImageManager;
use App\Services\Qr\QrImage;
use App\Services\Qr\QrSettings;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class QrStudioController extends Controller
{
    use ResolvesActiveRestaurant;

    public function __construct(
        private readonly QrSettings $settings,
        private readonly QrImage $image,
        private readonly ImageManager $images,
    ) {}

    public function edit(): InertiaResponse
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

    /**
     * Live preview image for the studio (settings passed as query values).
     */
    public function preview(Request $request): Response
    {
        $restaurant = $this->restaurant();
        Gate::authorize('update', $restaurant);

        $result = $this->image->png(
            $this->menuUrl($restaurant),
            $this->renderSettings($request, $restaurant),
            $this->image->logoPath($restaurant->logo),
            (int) config('qr.render.preview_size', 512),
        );

        return $this->respond($result);
    }

    /**
     * Downloadable PNG (default) or SVG export.
     */
    public function download(Request $request): Response
    {
        $restaurant = $this->restaurant();
        Gate::authorize('update', $restaurant);

        $settings = $this->renderSettings($request, $restaurant);
        $size = (int) config('qr.render.download_size', 1024);
        $logo = $this->image->logoPath($restaurant->logo);

        if ($request->query('format') === 'svg') {
            $result = $this->image->svg($this->menuUrl($restaurant), $settings, $logo, $size);
            $filename = 'qr-'.$restaurant->slug.'.svg';
        } else {
            $result = $this->image->png($this->menuUrl($restaurant), $settings, $logo, $size);
            $filename = 'qr-'.$restaurant->slug.'.png';
        }

        return $this->respond($result, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
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

        if ($qr !== null) {
            $qr->draft = null;
            $qr->save();
        }

        return back()->with('success', 'تم تجاهل المسودة.');
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function respond(ResultInterface $result, array $headers = []): Response
    {
        return response($result->getString(), 200, array_merge([
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'no-store, max-age=0',
        ], $headers));
    }

    /**
     * Merge the published settings with the requested overrides, keep only
     * known keys and validate before rendering.
     *
     * @return array<string, mixed>
     */
    private function renderSettings(Request $request, Restaurant $restaurant): array
    {
        $input = $this->settings->normalize(
            array_merge($this->settings->resolve($restaurant), $request->query()),
        );

        Validator::make($input, QrSettings::rules())->validate();

        $input['logo_enabled'] = filter_var($input['logo_enabled'] ?? false, FILTER_VALIDATE_BOOL);
        $input['quiet_zone'] = (int) ($input['quiet_zone'] ?? 4);
        $input['logo_ratio'] = (float) ($input['logo_ratio'] ?? 0.12);

        return $input;
    }

    private function menuUrl(Restaurant $restaurant): string
    {
        return route('menu.show', $restaurant->slug);
    }

    private function qrFor(Restaurant $restaurant): RestaurantQrCode
    {
        $qr = $restaurant->qrCode;

        if ($qr === null) {
            $qr = new RestaurantQrCode;
            $qr->restaurant_id = $restaurant->id;
        }

        return $qr;
    }
}
