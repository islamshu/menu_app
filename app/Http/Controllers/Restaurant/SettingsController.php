<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Restaurant\Concerns\ResolvesActiveRestaurant;
use App\Http\Requests\Restaurant\UpdateSettingsRequest;
use App\Models\Restaurant;
use App\Services\ImageManager;
use App\Services\Qr\QrImage;
use App\Services\Qr\QrSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    use ResolvesActiveRestaurant;

    /**
     * @var array<int, array{code: string, label: string}>
     */
    private const CURRENCIES = [
        ['code' => 'ILS', 'label' => 'شيكل إسرائيلي (₪)'],
        ['code' => 'USD', 'label' => 'دولار أمريكي ($)'],
        ['code' => 'EUR', 'label' => 'يورو (€)'],
        ['code' => 'GBP', 'label' => 'جنيه إسترليني (£)'],
        ['code' => 'SAR', 'label' => 'ريال سعودي (ر.س)'],
        ['code' => 'AED', 'label' => 'درهم إماراتي (د.إ)'],
        ['code' => 'EGP', 'label' => 'جنيه مصري (ج.م)'],
        ['code' => 'JOD', 'label' => 'دينار أردني (د.أ)'],
        ['code' => 'KWD', 'label' => 'دينار كويتي (د.ك)'],
        ['code' => 'QAR', 'label' => 'ريال قطري (ر.ق)'],
        ['code' => 'TRY', 'label' => 'ليرة تركية (₺)'],
    ];

    public function __construct(
        private readonly ImageManager $images,
        private readonly QrSettings $qrSettings,
        private readonly QrImage $qrImage,
    ) {}

    /**
     * Show the restaurant settings form (owner/manager only).
     */
    public function edit(): Response
    {
        $restaurant = $this->restaurant();
        Gate::authorize('update', $restaurant);

        return Inertia::render('Restaurant/Settings/Edit', [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'description' => $restaurant->description,
                'phone' => $restaurant->phone,
                'email' => $restaurant->email,
                'address' => $restaurant->address,
                'currency' => $restaurant->currency,
                'default_locale' => $restaurant->default_locale,
                'timezone' => $restaurant->timezone,
                'status' => $restaurant->status->value,
                'status_label' => $restaurant->status->label(),
                'logo' => $this->images->url($restaurant->logo),
                'cover_image' => $this->images->url($restaurant->cover_image),
            ],
            'menu_url' => route('menu.show', $restaurant->slug),
            'qr_image' => $this->qrDataUri($restaurant),
            'currencies' => self::CURRENCIES,
            'timezones' => timezone_identifiers_list(),
            'locales' => collect(config('locales.supported'))
                ->map(fn ($locale, $code) => ['code' => $code, 'label' => $locale['label']])
                ->values(),
        ]);
    }

    /**
     * Persist the restaurant settings (owner/manager only).
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $restaurant = $this->restaurant();
        $data = $request->validated();
        $directory = $this->images->directory($restaurant->id, 'branding');

        if ($request->hasFile('logo')) {
            $restaurant->logo = $this->images->store(
                $request->file('logo'),
                $directory,
                $restaurant->logo,
            );
        } elseif ($request->boolean('remove_logo')) {
            $this->images->delete($restaurant->logo, $directory);
            $restaurant->logo = null;
        }

        if ($request->hasFile('cover_image')) {
            $restaurant->cover_image = $this->images->store(
                $request->file('cover_image'),
                $directory,
                $restaurant->cover_image,
            );
        } elseif ($request->boolean('remove_cover')) {
            $this->images->delete($restaurant->cover_image, $directory);
            $restaurant->cover_image = null;
        }

        $restaurant->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'currency' => $data['currency'],
            'default_locale' => $data['default_locale'],
            'timezone' => $data['timezone'],
        ])->save();

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }

    /**
     * Render the published QR settings as an inline PNG data URI for the
     * settings-screen widget (rendered server-side, no client QR engine).
     */
    private function qrDataUri(Restaurant $restaurant): string
    {
        $settings = $this->qrSettings->resolve($restaurant);

        return $this->qrImage
            ->png(
                route('menu.show', $restaurant->slug),
                $settings,
                $this->qrImage->logoPath($restaurant->logo),
                (int) config('qr.render.preview_size', 512),
            )
            ->getDataUri();
    }
}
