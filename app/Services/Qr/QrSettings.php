<?php

namespace App\Services\Qr;

use App\Models\Restaurant;

class QrSettings
{
    public const KEYS = [
        'preset_key',
        'error_level',
        'module_style',
        'eye_style',
        'frame_style',
        'quiet_zone',
        'foreground_color',
        'background_color',
        'eye_color',
        'frame_label',
        'logo_enabled',
        'logo_ratio',
    ];

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
            'module_styles' => $this->labelled(config('qr.module_styles', [])),
            'eye_styles' => $this->labelled(config('qr.eye_styles', [])),
            'frame_styles' => $this->labelled(config('qr.frame_styles', [])),
            'sizes' => config('qr.sizes', []),
            'logo' => config('qr.logo', []),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     * @return array<int, array{value: string, label: array|string}>
     */
    protected function labelled(array $items): array
    {
        $collection = collect($items);

        return $collection
            ->map(fn ($item, $key) => [
                'value' => $key,
                'label' => $item['label'] ?? $key,
            ])
            ->values()
            ->all();
    }
}
