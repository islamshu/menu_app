/**
 * Shared presentation helpers for the public digital menu.
 *
 * Kept dependency-free and framework-agnostic so they can be unit-reasoned
 * about and reused by the hero, navigation, cards and modal alike.
 */

export { translate } from './i18n';

/**
 * Format a price using the restaurant currency and the active locale.
 * Degrades gracefully when the currency code is not supported by Intl.
 */
export function formatMoney(value, currency = 'USD', locale = 'ar') {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const amount = Number(value);

    if (Number.isNaN(amount)) {
        return '';
    }

    try {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(amount);
    } catch (error) {
        return `${amount.toFixed(2)} ${currency}`.trim();
    }
}

/**
 * Whether the compare-at price represents a genuine discount.
 */
export function hasDiscount(item) {
    const price = Number(item?.price);
    const compare = Number(item?.compare_at_price);

    return (
        Number.isFinite(price) &&
        Number.isFinite(compare) &&
        compare > price &&
        price >= 0
    );
}

/**
 * A stable DOM id for a category section, used for smooth scrolling.
 *
 * Virtual categories (e.g. the "Most Popular" category) expose their own
 * collision-free anchor; real categories stay namespaced under `cat-`.
 */
export function categoryAnchor(category) {
    if (category?.virtual) {
        return category.slug ?? category.id ?? 'most-popular';
    }

    return `cat-${category.slug ?? category.id}`;
}
