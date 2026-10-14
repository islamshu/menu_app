/**
 * The prop / emit contract every menu theme implements.
 *
 * Themes are pure presentation: the shared `MenuExperience` owns the data and
 * behaviour, while each theme decides how to compose the header, navigation,
 * cards and footer.
 */
export const themeProps = {
    restaurant: { type: Object, required: true },
    branding: { type: Object, required: true },
    cardStyle: { type: String, default: 'elevated' },
    categories: { type: Array, default: () => [] },
    featured: { type: Array, default: () => [] },
    virtualCategory: { type: Object, default: null },
    regularCategories: { type: Array, default: () => [] },
    hasMenu: { type: Boolean, default: false },
    locale: { type: String, default: 'ar' },
    locales: { type: Array, default: () => [] },
    dir: { type: String, default: 'rtl' },
    search: { type: String, default: '' },
    isSearching: { type: Boolean, default: false },
    searchResults: { type: Array, default: () => [] },
    activeId: { type: [Number, String, null], default: null },
    currency: { type: String, default: 'ILS' },
    preview: { type: Boolean, default: false },
};

export const themeEmits = [
    'open',
    'update:search',
    'update:locale',
    'navigate',
];
