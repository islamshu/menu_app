<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import MenuFooter from '@/Components/Menu/MenuFooter.vue';
import MenuHero from '@/Components/Menu/MenuHero.vue';
import MenuNav from '@/Components/Menu/MenuNav.vue';
import MenuProductCard from '@/Components/Menu/MenuProductCard.vue';
import MenuProductModal from '@/Components/Menu/MenuProductModal.vue';
import MenuSection from '@/Components/Menu/MenuSection.vue';
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
    meta: {
        type: Object,
        required: true,
    },
});

const locale = ref(props.restaurant.locale);
const dir = computed(
    () =>
        props.restaurant.locales.find((option) => option.code === locale.value)
            ?.dir ?? 'ltr',
);

const search = ref('');
const selectedItem = ref(null);
const activeId = ref(props.categories[0]?.id ?? null);

const query = computed(() => search.value.trim().toLowerCase());
const isSearching = computed(() => query.value.length > 0);

const allItems = computed(() =>
    props.categories.flatMap((category) => category.items),
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

const hasMenu = computed(() => props.categories.length > 0);

const applyLocale = (code) => {
    const option = props.restaurant.locales.find(
        (candidate) => candidate.code === code,
    );

    if (typeof document !== 'undefined') {
        document.documentElement.lang = code;
        document.documentElement.dir = option?.dir ?? 'ltr';
    }
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

    if (typeof document === 'undefined') {
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
    if (typeof IntersectionObserver === 'undefined') {
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
    setupObserver();
});

onBeforeUnmount(() => {
    observer?.disconnect();
});

watch(isSearching, () => {
    nextTick(setupObserver);
});

watch(locale, () => {
    nextTick(setupObserver);
});
</script>

<template>
    <div
        id="top"
        :dir="dir"
        :lang="locale"
        class="min-h-screen bg-paper font-sans text-ink"
    >
        <Head>
            <title>{{ meta.title }}</title>
            <meta
                head-key="description"
                name="description"
                :content="meta.description"
            />
            <meta head-key="og:title" property="og:title" :content="meta.title" />
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
        </Head>

        <MenuHero :restaurant="restaurant" :locale="locale" />

        <MenuNav
            :restaurant="restaurant"
            :categories="hasMenu ? categories : []"
            :locale="locale"
            :locales="restaurant.locales"
            :active-id="activeId"
            :search="search"
            @update:search="search = $event"
            @update:locale="setLocale"
            @navigate="navigate"
        />

        <main class="mx-auto max-w-6xl px-4 pb-24">
            <template v-if="isSearching">
                <div class="pt-10">
                    <p class="text-sm text-ink-muted" role="status">
                        {{
                            locale === 'ar'
                                ? `نتائج البحث عن «${search.trim()}» (${searchResults.length})`
                                : `Results for “${search.trim()}” (${searchResults.length})`
                        }}
                    </p>

                    <div
                        v-if="searchResults.length"
                        class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <MenuProductCard
                            v-for="item in searchResults"
                            :key="item.id"
                            :item="item"
                            :locale="locale"
                            :currency="restaurant.currency"
                            @open="openItem"
                        />
                    </div>

                    <MenuEmpty
                        v-else
                        class="mt-6"
                        icon="search"
                        :title="
                            locale === 'ar'
                                ? 'لا توجد نتائج مطابقة'
                                : 'No matching dishes'
                        "
                        :description="
                            locale === 'ar'
                                ? 'جرّب كلمة أخرى أو تصفّح التصنيفات.'
                                : 'Try another keyword or browse the categories.'
                        "
                    />
                </div>
            </template>

            <template v-else>
                <section
                    v-if="featured.length"
                    class="pt-10"
                    :aria-label="
                        locale === 'ar' ? 'أطباق مميزة' : 'Featured dishes'
                    "
                >
                    <div class="mb-6 flex items-center gap-4">
                        <h2
                            class="font-display text-2xl leading-tight text-ink sm:text-3xl"
                        >
                            {{ locale === 'ar' ? 'أطباق مميزة' : 'Featured' }}
                        </h2>
                        <span
                            class="h-px flex-1 bg-ink-line"
                            aria-hidden="true"
                        />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <MenuProductCard
                            v-for="item in featured"
                            :key="item.id"
                            :item="item"
                            :locale="locale"
                            :currency="restaurant.currency"
                            @open="openItem"
                        />
                    </div>
                </section>

                <div
                    v-if="hasMenu"
                    :class="featured.length ? 'mt-16 space-y-16' : 'mt-10 space-y-16'"
                >
                    <MenuSection
                        v-for="(category, index) in categories"
                        :key="category.id"
                        :category="category"
                        :index="index + 1"
                        :locale="locale"
                        :currency="restaurant.currency"
                        @open="openItem"
                    />
                </div>

                <MenuEmpty
                    v-else
                    class="mt-10"
                    icon="clock"
                    :title="
                        locale === 'ar'
                            ? 'القائمة قيد الإعداد'
                            : 'The menu is being prepared'
                    "
                    :description="
                        locale === 'ar'
                            ? 'لم تتم إضافة أي أصناف إلى هذه القائمة بعد. يرجى العودة قريباً.'
                            : 'No dishes have been added to this menu yet. Please check back soon.'
                    "
                />
            </template>
        </main>

        <MenuFooter :restaurant="restaurant" :locale="locale" />

        <MenuProductModal
            :item="selectedItem"
            :locale="locale"
            :currency="restaurant.currency"
            @close="closeItem"
        />
    </div>
</template>
