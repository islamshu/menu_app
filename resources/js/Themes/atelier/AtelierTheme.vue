<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import AtelierProductCard from './AtelierProductCard.vue';
import { themeEmits, themeProps } from '@/Themes/props';
import { categoryAnchor, translate } from '@/Support/menu';

const props = defineProps(themeProps);
const emit = defineEmits(themeEmits);

const description = computed(() =>
    translate(
        props.restaurant.description,
        props.locale,
        props.restaurant.locale,
    ),
);

const hasContact = computed(
    () =>
        props.restaurant.phone ||
        props.restaurant.email ||
        props.restaurant.address,
);

const showLogo = computed(() => props.branding.logo_display_mode !== 'hidden');

const logoShape = computed(() => {
    switch (props.branding.logo_display_mode) {
        case 'circle':
            return 'rounded-full';
        case 'square':
            return 'rounded-none';
        default:
            return 'rounded-2xl';
    }
});

const sectionStart = computed(() => (props.virtualCategory ? 2 : 1));

const label = (value) => (props.locale === 'ar' ? value.ar : value.en);

const categoryName = (category) =>
    translate(category.name, props.locale, props.restaurant.locale);

const categoryDescription = (category) =>
    translate(category.description, props.locale, props.restaurant.locale);
</script>

<template>
    <div class="bg-[var(--brand-bg)] text-[var(--brand-text)]">
        <header class="px-4 pt-10 sm:px-6 lg:pt-16">
            <div
                class="mx-auto grid max-w-6xl items-center gap-8 lg:grid-cols-[1.05fr_1fr] lg:gap-14"
            >
                <div>
                    <span
                        v-if="showLogo && restaurant.logo"
                        class="inline-flex h-16 w-16 items-center justify-center overflow-hidden bg-[var(--brand-surface)] shadow-sm"
                        :class="logoShape"
                    >
                        <img
                            :src="restaurant.logo"
                            :alt="restaurant.name"
                            class="h-full w-full object-cover"
                        />
                    </span>

                    <p
                        class="mt-5 text-[11px] font-semibold uppercase tracking-[0.4em] text-[var(--brand-primary)]"
                    >
                        {{ label({ ar: 'القائمة الرقمية', en: 'Digital Menu' }) }}
                    </p>

                    <h1
                        class="mt-3 font-brand-heading text-4xl leading-[1.05] sm:text-5xl lg:text-6xl"
                    >
                        {{ restaurant.name }}
                    </h1>

                    <span
                        class="mt-6 block h-px w-24 bg-[var(--brand-primary)]"
                        aria-hidden="true"
                    />

                    <p
                        v-if="description"
                        class="mt-5 max-w-xl text-sm leading-relaxed text-[var(--brand-muted)] sm:text-base"
                    >
                        {{ description }}
                    </p>

                    <div
                        v-if="hasContact"
                        class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm"
                    >
                        <a
                            v-if="restaurant.phone"
                            :href="`tel:${restaurant.phone}`"
                            dir="ltr"
                            class="inline-flex items-center gap-2 text-[var(--brand-text)] transition hover:text-[var(--brand-primary)]"
                        >
                            <Icon
                                name="phone"
                                class="h-4 w-4 text-[var(--brand-primary)]"
                            />
                            {{ restaurant.phone }}
                        </a>
                        <a
                            v-if="restaurant.email"
                            :href="`mailto:${restaurant.email}`"
                            dir="ltr"
                            class="inline-flex items-center gap-2 text-[var(--brand-text)] transition hover:text-[var(--brand-primary)]"
                        >
                            <Icon
                                name="mail"
                                class="h-4 w-4 text-[var(--brand-primary)]"
                            />
                            {{ restaurant.email }}
                        </a>
                        <span
                            v-if="restaurant.address"
                            class="inline-flex items-center gap-2 text-[var(--brand-muted)]"
                        >
                            <Icon
                                name="pin"
                                class="h-4 w-4 text-[var(--brand-primary)]"
                            />
                            {{ restaurant.address }}
                        </span>
                    </div>
                </div>

                <div
                    class="relative aspect-[4/3] overflow-hidden rounded-[var(--brand-radius)] bg-[var(--brand-border)]/40 shadow-[0_30px_60px_-45px_rgba(44,38,34,0.8)]"
                >
                    <img
                        v-if="restaurant.cover_image"
                        :src="restaurant.cover_image"
                        :alt="restaurant.name"
                        class="h-full w-full object-cover"
                        fetchpriority="high"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--brand-accent)]/50 via-[var(--brand-bg)] to-[var(--brand-secondary)]/30"
                    >
                        <span
                            class="font-brand-heading text-6xl text-[var(--brand-primary)]/70"
                        >
                            {{ restaurant.name.charAt(0) }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <div
            class="sticky top-0 z-40 mt-10 border-y border-[var(--brand-border)] bg-[var(--brand-bg)]/95 backdrop-blur-md"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex items-center gap-3 py-3">
                    <span
                        class="truncate font-brand-heading text-lg"
                    >
                        {{ restaurant.name }}
                    </span>

                    <label class="relative ms-auto hidden md:block">
                        <span class="sr-only">
                            {{
                                label({
                                    ar: 'البحث في القائمة',
                                    en: 'Search the menu',
                                })
                            }}
                        </span>
                        <Icon
                            name="search"
                            class="pointer-events-none absolute inset-y-0 start-3 my-auto h-4 w-4 text-[var(--brand-muted)]"
                        />
                        <input
                            type="search"
                            :value="search"
                            :placeholder="
                                label({ ar: 'ابحث عن طبق...', en: 'Search a dish...' })
                            "
                            class="w-56 rounded-full border border-[var(--brand-border)] bg-[var(--brand-surface)] py-2 ps-9 pe-4 text-sm placeholder:text-[var(--brand-muted)] focus:border-[var(--brand-primary)] focus:outline-none lg:w-64"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>

                    <div
                        class="flex items-center gap-0.5 rounded-full border border-[var(--brand-border)] p-0.5"
                        role="group"
                    >
                        <button
                            v-for="option in locales"
                            :key="option.code"
                            type="button"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold transition"
                            :class="
                                locale === option.code
                                    ? 'bg-[var(--brand-primary)] text-[var(--brand-on-primary)]'
                                    : 'text-[var(--brand-muted)] hover:text-[var(--brand-text)]'
                            "
                            :aria-pressed="locale === option.code"
                            @click="emit('update:locale', option.code)"
                        >
                            {{ option.code.toUpperCase() }}
                        </button>
                    </div>
                </div>

                <nav
                    v-if="categories.length && !search"
                    class="brand-scroll flex gap-6 overflow-x-auto"
                >
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        class="relative shrink-0 whitespace-nowrap py-3 text-sm font-medium transition"
                        :class="
                            activeId === category.id
                                ? 'text-[var(--brand-primary)]'
                                : 'text-[var(--brand-muted)] hover:text-[var(--brand-text)]'
                        "
                        :aria-current="
                            activeId === category.id ? 'true' : undefined
                        "
                        @click="emit('navigate', category)"
                    >
                        {{ categoryName(category) }}
                        <span
                            v-if="activeId === category.id"
                            class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-[var(--brand-primary)]"
                            aria-hidden="true"
                        />
                    </button>
                </nav>
            </div>
        </div>

        <main class="mx-auto max-w-6xl px-4 pb-24 sm:px-6">
            <template v-if="isSearching">
                <div class="pt-12">
                    <p class="font-brand-heading text-2xl">
                        {{
                            label({
                                ar: `نتائج «${search.trim()}»`,
                                en: `Results for “${search.trim()}”`,
                            })
                        }}
                    </p>
                    <p class="mt-1 text-sm text-[var(--brand-muted)]" role="status">
                        {{ searchResults.length }}
                        {{ label({ ar: 'نتيجة', en: 'results' }) }}
                    </p>

                    <div
                        v-if="searchResults.length"
                        class="mt-8 grid gap-3 sm:grid-cols-2"
                    >
                        <AtelierProductCard
                            v-for="item in searchResults"
                            :key="item.id"
                            :item="item"
                            :locale="locale"
                            :currency="currency"
                            :card-style="cardStyle"
                            @open="emit('open', $event)"
                        />
                    </div>

                    <MenuEmpty
                        v-else
                        class="mt-8"
                        icon="search"
                        :title="
                            label({
                                ar: 'لا توجد نتائج مطابقة',
                                en: 'No matching dishes',
                            })
                        "
                        :description="
                            label({
                                ar: 'جرّب كلمة أخرى أو تصفّح التصنيفات.',
                                en: 'Try another keyword or browse the categories.',
                            })
                        "
                    />
                </div>
            </template>

            <template v-else>
                <div v-if="hasMenu || featured.length" class="mt-14 space-y-16">
                    <section
                        v-if="featured.length"
                        :aria-label="label({ ar: 'أطباق مميزة', en: 'Featured' })"
                    >
                        <div class="mb-6 flex items-baseline gap-3">
                            <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                {{ label({ ar: 'أطباق مميزة', en: 'Featured' }) }}
                            </h2>
                            <span class="h-px flex-1 bg-[var(--brand-border)]" />
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <AtelierProductCard
                                v-for="item in featured"
                                :key="item.id"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
                                :card-style="cardStyle"
                                @open="emit('open', $event)"
                            />
                        </div>
                    </section>

                    <section
                        v-if="virtualCategory"
                        :id="categoryAnchor(virtualCategory)"
                        class="scroll-mt-36"
                    >
                        <div class="mb-6 flex items-baseline gap-3">
                            <h2
                                class="font-brand-heading text-2xl text-[var(--brand-primary)] sm:text-3xl"
                            >
                                {{ categoryName(virtualCategory) }}
                            </h2>
                            <span
                                class="h-px flex-1 bg-[var(--brand-primary)]/40"
                            />
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <AtelierProductCard
                                v-for="item in virtualCategory.items"
                                :key="item.id"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
                                :card-style="cardStyle"
                                @open="emit('open', $event)"
                            />
                        </div>
                    </section>

                    <section
                        v-for="(category, index) in regularCategories"
                        :key="category.id"
                        :id="categoryAnchor(category)"
                        class="scroll-mt-36"
                    >
                        <div
                            class="mb-6 grid gap-3 border-t border-[var(--brand-border)] pt-6 sm:grid-cols-[auto_1fr] sm:items-baseline sm:gap-6"
                        >
                            <span
                                class="font-brand-heading text-3xl text-[var(--brand-primary)]/70 tabular-nums"
                                aria-hidden="true"
                            >
                                {{ String(index + sectionStart).padStart(2, '0') }}
                            </span>
                            <div>
                                <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                    {{ categoryName(category) }}
                                </h2>
                                <p
                                    v-if="categoryDescription(category)"
                                    class="mt-1 max-w-2xl text-sm text-[var(--brand-muted)]"
                                >
                                    {{ categoryDescription(category) }}
                                </p>
                            </div>
                        </div>

                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="categoryName(category)"
                            loading="lazy"
                            decoding="async"
                            class="mb-6 h-48 w-full rounded-[var(--brand-radius)] object-cover sm:h-64"
                        />

                        <div class="grid gap-3 sm:grid-cols-2">
                            <AtelierProductCard
                                v-for="item in category.items"
                                :key="item.id"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
                                :card-style="cardStyle"
                                @open="emit('open', $event)"
                            />
                        </div>
                    </section>
                </div>

                <MenuEmpty
                    v-else
                    class="mt-12"
                    icon="clock"
                    :title="
                        label({
                            ar: 'القائمة قيد الإعداد',
                            en: 'The menu is being prepared',
                        })
                    "
                    :description="
                        label({
                            ar: 'لم تتم إضافة أي أصناف بعد. يرجى العودة قريباً.',
                            en: 'No dishes have been added yet. Please check back soon.',
                        })
                    "
                />
            </template>
        </main>

        <footer class="border-t border-[var(--brand-border)] px-5 py-14">
            <div class="mx-auto max-w-6xl text-center">
                <p class="font-brand-heading text-2xl">{{ restaurant.name }}</p>
                <span
                    class="mx-auto mt-4 block h-px w-16 bg-[var(--brand-primary)]"
                    aria-hidden="true"
                />
                <p class="mt-4 text-xs text-[var(--brand-muted)]">
                    {{
                        label({
                            ar: 'هذه القائمة للعرض فقط — لا تتضمن طلباً أو دفعاً إلكترونياً.',
                            en: 'This menu is for display only — no ordering or online payment.',
                        })
                    }}
                </p>
                <p
                    class="mt-6 text-[11px] uppercase tracking-[0.3em] text-[var(--brand-muted)]"
                >
                    {{ new Date().getFullYear() }}
                </p>
            </div>
        </footer>
    </div>
</template>
