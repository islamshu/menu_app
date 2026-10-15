<?php

namespace App\Services\Qr;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;

class QrSettings
{
    public const KEYS = [
        'preset_key',
        'error_level',
        'quiet_zone',
        'foreground_color',
        'background_color',
        'logo_enabled',
        'logo_ratio',
    ];

    /**
     * Validation rules shared by the studio form request and the render
     * endpoints (which receive the settings as query string values).
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $hex = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];

        return [
            'preset_key' => ['required', 'string', Rule::in(array_keys(config('qr.presets', [])))],
            'error_level' => ['required', 'string', Rule::in(array_keys(config('qr.error_levels', [])))],
            'quiet_zone' => ['required', 'integer', 'min:0', 'max:8'],
            'foreground_color' => $hex,
            'background_color' => $hex,
            'logo_enabled' => ['boolean'],
            'logo_ratio' => [
                'required',
                'numeric',
                'min:'.config('qr.logo.min_ratio', 0.08),
                'max:'.config('qr.logo.max_ratio', 0.15),
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function presets(): array
    {
        return config('qr.presets', []);
    }

    public function defaultPresetKey(): string
    {
        $default = config('qr.default_preset');

        if (is_string($default) && array_key_exists($default, $this->presets())) {
            return $default;
        }

        return array_key_first($this->presets()) ?? 'noir-luxe';
    }

    public function presetExists(string $key): bool
    {
        return array_key_exists($key, $this->presets());
    }

    /**
     * @return array<string, mixed>
     */
    public function presetDefaults(string $presetKey): array
    {
        if (! $this->presetExists($presetKey)) {
            $presetKey = $this->defaultPresetKey();
        }

        $defaults = $this->presets()[$presetKey]['defaults'] ?? config('qr.defaults', []);

        return array_merge((array) config('qr.defaults', []), $defaults, ['preset_key' => $presetKey]);
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(Restaurant $restaurant): array
    {
        return $this->apply($restaurant->qrCode?->published ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function draft(Restaurant $restaurant): array
    {
        $draft = $restaurant->qrCode?->draft;

        if (! is_array($draft) || $draft === []) {
            return $this->resolve($restaurant);
        }

        return $this->apply($draft);
    }

    public function hasDraft(Restaurant $restaurant): bool
    {
        $draft = $restaurant->qrCode?->draft;

        return is_array($draft) && $draft !== [];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalize(array $values): array
    {
        return $this->onlyKnown($values);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function apply(array $values): array
    {
        $values = $this->onlyKnown($values);

        $presetKey = $values['preset_key'] ?? $this->defaultPresetKey();
        if (! is_string($presetKey) || ! $this->presetExists($presetKey)) {
            $presetKey = $this->defaultPresetKey();
        }

        return array_merge($this->presetDefaults($presetKey), $values, ['preset_key' => $presetKey]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function onlyKnown(array $values): array
    {
        return array_intersect_key($values, array_flip(self::KEYS));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function presetsForFrontend(): array
    {
        return collect($this->presets())
            ->map(fn (array $preset, string $key) => [
                'key' => $key,
                'label' => $preset['label'] ?? ['ar' => $key, 'en' => $key],
                'description' => $preset['description'] ?? ['ar' => '', 'en' => ''],
                'defaults' => $this->presetDefaults($key),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return [
            'default_preset' => $this->defaultPresetKey(),
            'presets' => $this->presetsForFrontend(),
            'error_levels' => collect(config('qr.error_levels', []))
                ->map(fn ($data, $k) => array_merge(['value' => $k], $data))
                ->values()
                ->all(),
            'sizes' => config('qr.sizes', []),
            'logo' => config('qr.logo', []),
        ];
    }
}
