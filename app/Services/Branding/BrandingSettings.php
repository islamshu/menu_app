<?php

namespace App\Services\Branding;

use App\Models\Restaurant;
use Illuminate\Support\Collection;

/**
 * Resolves, normalises and describes the branding configuration.
 *
 * The class is the single PHP entry point for theme defaults, the published
 * ("live") settings and the working draft, so the public menu, the studio and
 * validation all agree on exactly the same shape.
 */
class BrandingSettings
{
    /**
     * Every persisted branding key, in a stable order.
     *
     * @var array<int, string>
     */
    public const KEYS = [
        'theme_key',
        'primary_color',
        'secondary_color',
        'accent_color',
        'background_color',
        'text_color',
        'heading_font',
        'body_font',
        'border_radius',
        'card_style',
        'logo_display_mode',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function themes(): array
    {
        return config('branding.themes', []);
    }

    public function defaultThemeKey(): string
    {
        $default = config('branding.default_theme');

        if (is_string($default) && array_key_exists($default, $this->themes())) {
            return $default;
        }

        return array_key_first($this->themes()) ?? 'noir-luxe';
    }

    /**
     * Whether the given theme key is part of the registry.
     */
    public function themeExists(string $key): bool
    {
        return array_key_exists($key, $this->themes());
    }

    /**
     * The default settings for a theme, falling back to the default theme.
     *
     * @return array<string, mixed>
     */
    public function themeDefaults(string $themeKey): array
    {
        if (! $this->themeExists($themeKey)) {
            $themeKey = $this->defaultThemeKey();
        }

        $defaults = $this->themes()[$themeKey]['defaults'] ?? [];

        return array_merge($defaults, ['theme_key' => $themeKey]);
    }

    /**
     * The published ("live") settings for a restaurant, always complete.
     *
     * @return array<string, mixed>
     */
    public function resolve(Restaurant $restaurant): array
    {
        return $this->apply($restaurant->branding?->published ?? []);
    }

    /**
     * The working draft settings, falling back to the published settings.
     *
     * @return array<string, mixed>
     */
    public function draft(Restaurant $restaurant): array
    {
        $draft = $restaurant->branding?->draft;

        if (! is_array($draft) || $draft === []) {
            return $this->resolve($restaurant);
        }

        return $this->apply($draft);
    }

    /**
     * Whether the restaurant currently has an unpublished draft.
     */
    public function hasDraft(Restaurant $restaurant): bool
    {
        $draft = $restaurant->branding?->draft;

        return is_array($draft) && $draft !== [];
    }

    /**
     * Keep only the known branding keys from raw (already validated) input.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function normalize(array $values): array
    {
        return $this->onlyKnown($values);
    }

    /**
     * Merge raw values over the defaults of their selected theme.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function apply(array $values): array
    {
        $values = $this->onlyKnown($values);

        $themeKey = $values['theme_key'] ?? $this->defaultThemeKey();

        if (! is_string($themeKey) || ! $this->themeExists($themeKey)) {
            $themeKey = $this->defaultThemeKey();
        }

        return array_merge(
            $this->themeDefaults($themeKey),
            $values,
            ['theme_key' => $themeKey],
        );
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
     * The theme registry in the shape the studio expects.
     *
     * @return array<int, array<string, mixed>>
     */
    public function themesForFrontend(): array
    {
        return collect($this->themes())
            ->map(fn (array $theme, string $key) => [
                'key' => $key,
                'label' => $theme['label'] ?? ['ar' => $key, 'en' => $key],
                'description' => $theme['description'] ?? ['ar' => '', 'en' => ''],
                'preview' => $theme['preview'] ?? ['from' => '#000000', 'to' => '#ffffff'],
                'defaults' => $this->themeDefaults($key),
            ])
            ->values()
            ->all();
    }

    /**
     * A trimmed config surface shared with the frontend.
     *
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return [
            'default_theme' => $this->defaultThemeKey(),
            'fonts' => collect(config('branding.fonts', []))
                ->map(fn (array $font, string $key) => [
                    'value' => $key,
                    'label' => $font['label'] ?? $key,
                    'stack' => $font['stack'] ?? $key,
                    'bunny' => $font['bunny'] ?? null,
                ])
                ->values()
                ->all(),
            'radii' => collect(config('branding.radii', []))
                ->map(fn (array $radius, string $key) => [
                    'value' => $key,
                    'label' => $radius['label'] ?? $key,
                    'css' => $radius['value'] ?? '0px',
                ])
                ->values()
                ->all(),
            'card_styles' => $this->labelled(config('branding.card_styles', [])),
            'logo_modes' => $this->labelled(config('branding.logo_modes', [])),
            'palettes' => collect(config('branding.palettes', []))
                ->map(fn (array $palette) => [
                    'key' => $palette['key'] ?? '',
                    'label' => $palette['label'] ?? [],
                    'colors' => $palette['colors'] ?? [],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     * @return array<int, array{value: string, label: string}>
     */
    protected function labelled(array $items): array
    {
        /** @var Collection<string, array<string, mixed>> $collection */
        $collection = collect($items);

        return $collection
            ->map(fn (array $item, string $key) => [
                'value' => $key,
                'label' => $item['label'] ?? $key,
            ])
            ->values()
            ->all();
    }
}
