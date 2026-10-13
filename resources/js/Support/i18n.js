/**
 * Shared i18n helpers.
 *
 * Kept dependency-free and framework-agnostic so both the dashboard and the
 * public menu can resolve translated fields the same way.
 */

/**
 * Resolve a translated field (JSON map like { ar, en }) for a locale.
 * Falls back to the fallback locale, then the first non-empty value.
 */
export function translate(field, locale = 'ar', fallbackLocale = 'ar') {
    if (!field) {
        return '';
    }

    if (typeof field === 'string') {
        return field;
    }

    return (
        field[locale] ||
        field[fallbackLocale] ||
        Object.values(field).find((value) => value) ||
        ''
    );
}
