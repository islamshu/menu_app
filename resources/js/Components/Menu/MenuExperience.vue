<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import MenuProductModal from '@/Components/Menu/MenuProductModal.vue';
import { resolveTheme } from '@/Themes/registry';
import { buildTokens, googleFontsUrl } from '@/Support/branding';
import { categoryAnchor, translate } from '@/Support/menu';

const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    featured: {
        type: Array,
        default: () => [],
    },
    branding: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        default: null,
    },
    preview: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const options = computed(() => page.props.brandingOptions ?? {});

const locale = ref(props.restaurant.locale);
const locales = computed(() => props.restaurant.locales ?? []);
const dir = computed(
    () =>
        locales.value.find((option) => option.code === locale.value)?.dir ??
        'ltr',
);

const search = ref('');
const selectedItem = ref(null);
const activeId = ref(props.categories[0]?.id ?? null);

const tokens = computed(() => buildTokens(props.branding, options.value));
const themeComponent = computed(() => resolveTheme(props.branding.theme_key));
const fontsUrl = computed(() =>
    googleFontsUrl(options.value, [
        props.branding.heading_font,
        props.branding.body_font,
    ]),
);

const query = computed(() => search.value.trim().toLowerCase());
const isSearching = computed(() => query.value.length > 0);

const virtualCategory = computed(
    () => props.categories.find((category) => category.virtual === true) ?? null,
);

const regularCategories = computed(() =>
    props.categories.filter((category) => category.virtual !== true),
);

const allItems = computed(() =>
    regularCategories.value.flatMap((category) => category.items),
);

const searchResults = computed(() => {
    if (!isSearching.value) {
        return [];
    }

    return allItems.value.filter((item) => {
        const name = translate(item.name, locale.value).toLowerCase();
        const description = translate(
            item.description,
            locale.value,
        ).toLowerCase();

        return name.includes(query.value) || description.includes(query.value);
    });
});

const hasMenu = computed(() => regularCategories.value.length > 0);

const themeProps = computed(() => ({
    restaurant: props.restaurant,
    branding: props.branding,
    cardStyle: props.branding.card_style,
    categories: props.categories,
    featured: props.featured,
    virtualCategory: virtualCategory.value,
    regularCategories: regularCategories.value,
    hasMenu: hasMenu.value,
    locale: locale.value,
    locales: locales.value,
    dir: dir.value,
    search: search.value,
    isSearching: isSearching.value,
    searchResults: searchResults.value,
    activeId: activeId.value,
    currency: props.restaurant.currency,
    preview: props.preview,
}));

const applyLocale = (code) => {
    if (props.preview || typeof document === 'undefined') {
        return;
    }

    const option = locales.value.find((candidate) => candidate.code === code);

    document.documentElement.lang = code;
    document.documentElement.dir = option?.dir ?? 'ltr';
};

const setLocale = (code) => {
    if (code === locale.value) {
        return;
    }

    locale.value = code;
    applyLocale(code);
};

const openItem = (item) => {
    selectedItem.value = item;
};

const closeItem = () => {
    selectedItem.value = null;
};

const navigate = (category) => {
    activeId.value = category.id;

    if (props.preview || typeof document === 'undefined') {
        return;
    }

    const target = document.getElementById(categoryAnchor(category));

    if (!target) {
        return;
    }

    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    target.scrollIntoView({
        behavior: reduceMotion ? 'auto' : 'smooth',
        block: 'start',
    });
};

let observer = null;

const setupObserver = () => {
    if (
        props.preview ||
        typeof IntersectionObserver === 'undefined' ||
        typeof document === 'undefined'
    ) {
        return;
    }

    observer?.disconnect();
    observer = null;

    if (isSearching.value) {
        return;
    }

    const sections = props.categories
        .map((category) => document.getElementById(categoryAnchor(category)))
        .filter(Boolean);

    if (!sections.length) {
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (a, b) =>
                        a.boundingClientRect.top - b.boundingClientRect.top,
                );

            if (!visible.length) {
                return;
            }

            const match = props.categories.find(
                (category) =>
                    categoryAnchor(category) === visible[0].target.id,
            );

            if (match) {
                activeId.value = match.id;
            }
        },
        { rootMargin: '-45% 0px -50% 0px', threshold: 0 },
    );

    sections.forEach((section) => observer.observe(section));
};

onMounted(() => {
    applyLocale(locale.value);
    nextTick(setupObserver);
});

onBeforeUnmount(() => {
    observer?.disconnect();
});

watch(isSearching, () => nextTick(setupObserver));
watch(locale, () => nextTick(setupObserver));
</script>

<template>
    <div
        id="top"
        :dir="dir"
        :lang="locale"
        :style="tokens"
        class="min-h-screen bg-[var(--brand-bg)] font-brand-body text-[var(--brand-text)]"
    >
        <Head>
            <template v-if="fontsUrl">
                <link
                    head-key="brand-fonts-preconnect"
                    rel="preconnect"
                    href="https://fonts.bunny.net"
                />
                <link
                    head-key="brand-fonts"
                    rel="stylesheet"
                    :href="fontsUrl"
                />
            </template>

            <template v-if="meta">
                <title>{{ meta.title }}</title>
                <meta
                    head-key="description"
                    name="description"
                    :content="meta.description"
                />
                <meta
                    head-key="og:title"
                    property="og:title"
                    :content="meta.title"
                />
                <meta
                    head-key="og:description"
                    property="og:description"
                    :content="meta.description"
                />
                <meta
                    v-if="meta.image"
                    head-key="og:image"
                    property="og:image"
                    :content="meta.image"
                />
                <meta
                    head-key="og:type"
                    property="og:type"
                    :content="meta.type || 'website'"
                />
                <meta
                    head-key="og:locale"
                    property="og:locale"
                    :content="locale"
                />
                <link
                    head-key="canonical"
                    rel="canonical"
                    :href="meta.canonical"
                />
            </template>
        </Head>

        <component
            :is="themeComponent"
            v-bind="themeProps"
            @open="openItem"
            @update:search="search = $event"
            @update:locale="setLocale"
            @navigate="navigate"
        />

        <MenuProductModal
            :item="selectedItem"
            :locale="locale"
            :currency="restaurant.currency"
            @close="closeItem"
        />
    </div>
</template>
