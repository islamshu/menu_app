<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasTranslations
{
    /**
     * Resolve a translated attribute value for the given (or current) locale.
     *
     * Falls back to the configured default locale, then to the first
     * non-empty value available. Accepts either a JSON array attribute or a
     * plain string for backwards compatibility.
     */
    public function translation(string $field, ?string $locale = null): ?string
    {
        $values = $this->getAttribute($field);

        if (is_string($values)) {
            return $values !== '' ? $values : null;
        }

        if (! is_array($values)) {
            return null;
        }

        $locale ??= config('locales.default');

        if (! empty($values[$locale])) {
            return $values[$locale];
        }

        $default = config('locales.default');

        if (! empty($values[$default])) {
            return $values[$default];
        }

        foreach ($values as $value) {
            if (! empty($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * The raw translation map for a field.
     *
     * @return array<string, string|null>
     */
    public function translationsFor(string $field): array
    {
        $values = $this->getAttribute($field);

        return is_array($values) ? $values : [];
    }

    /**
     * Build a locale-agnostic slug from the best available name.
     */
    public function slugSource(string $field, string $fallback): string
    {
        $source = $this->translation($field, 'en')
            ?? $this->translation($field, config('locales.default'));

        return Str::slug((string) $source) ?: $fallback;
    }
}
