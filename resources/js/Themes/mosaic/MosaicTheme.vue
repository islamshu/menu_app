<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import MosaicProductCard from './MosaicProductCard.vue';
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

const sectionStart = computed(() => (props.virtualCategory ? 2 : 1));

const label = (value) => (props.locale === 'ar' ? value.ar : value.en);

const pad = (index) => String(index).padStart(2, '0');

const categoryName = (category) =>
    translate(category.name, props.locale, props.restaurant.locale);

const categoryDescription = (category) =>
    translate(category.description, props.locale, props.restaurant.locale);
</script>

<template>
    <div class="bg-[var(--brand-bg)] text-[var(--brand-text)]">
        <header class="relative overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-b from-[var(--brand-primary)]/25 via-[var(--brand-bg)] to-[var(--brand-bg)]"
                aria-hidden="true"
            />
            <div
                class="absolute inset-x-0 top-0 h-3 bg-[repeating-linear-gradient(45deg,var(--brand-primary)_0_8px,var(--brand-accent)_8px_16px)]"
                aria-hidden="true"
            />

            <div class="relative mx-auto max-w-4xl px-5 pt-16 pb-10 text-center">
                <span
                    v-if="showLogo"
                    class="mx-auto flex h-28 w-28 items-center justify-center overflow-hidden rounded-t-full border-2 border-[var(--brand-primary)]/50 bg-[var(--brand-surface)] shadow-[0_24px_60px_-34px_rgba(0,0,0,0.75)]"
                >
                    <img
                        v-if="restaurant.logo"
                        :src="restaurant.logo"
                        :alt="restaurant.name"
                        class="h-full w-full object-cover"
                    />
                    <span
                        v-else
                        class="font-brand-heading text-4xl text-[var(--brand-primary)]"
                    >
                        {{ restaurant.name.charAt(0) }}
                    </span>
                </span>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <span
                        class="h-1.5 w-1.5 rotate-45 bg-[var(--brand-secondary)]"
                        aria-hidden="true"
                    />
                    <p
                        class="text-[11px] font-semibold uppercase tracking-[0.45em] text-[var(--brand-secondary)]"
                    >
                        {{ label({ ar: 'قائمة الطعام', en: 'The Menu' }) }}
                    </p>
                    <span
                        class="h-1.5 w-1.5 rotate-45 bg-[var(--brand-secondary)]"
                        aria-hidden="true"
                    />
                </div>

                <h1
                    class="mt-4 font-brand-heading text-4xl leading-tight sm:text-5xl lg:text-6xl"
                >
                    {{ restaurant.name }}
                </h1>

                <p
                    v-if="description"
                    class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-[var(--brand-muted)] sm:text-base"
                >
                    {{ description }}
                </p>

                <div
                    v-if="hasContact"
                    class="mt-8 flex flex-wrap items-center justify-center gap-3 text-xs sm:text-sm"
                >
                    <a
                        v-if="restaurant.phone"
                        :href="`tel:${restaurant.phone}`"
                        dir="ltr"
                        class="inline-flex items-center gap-2 rounded-full bg-[var(--brand-primary)]/10 px-4 py-2 text-[var(--brand-text)] ring-1 ring-inset ring-[var(--brand-primary)]/30 transition hover:bg-[var(--brand-primary)]/20"
                    >
                        <Icon name="phone" class="h-4 w-4 text-[var(--brand-primary)]" />
                        {{ restaurant.phone }}
                    </a>

                    <a
                        v-if="restaurant.email"
                        :href="`mailto:${restaurant.email}`"
                        dir="ltr"
                        class="inline-flex items-center gap-2 rounded-full bg-[var(--brand-primary)]/10 px-4 py-2 text-[var(--brand-text)] ring-1 ring-inset ring-[var(--brand-primary)]/30 transition hover:bg-[var(--brand-primary)]/20"
                    >
                        <Icon name="mail" class="h-4 w-4 text-[var(--brand-primary)]" />
                        {{ restaurant.email }}
                    </a>

                    <span
                        v-if="restaurant.address"
                        class="inline-flex items-center gap-2 rounded-full bg-[var(--brand-primary)]/10 px-4 py-2 text-[var(--brand-text)] ring-1 ring-inset ring-[var(--brand-primary)]/30"
                    >
                        <Icon name="pin" class="h-4 w-4 text-[var(--brand-primary)]" />
                        {{ restaurant.address }}
                    </span>
                </div>
            </div>
        </header>

        <div
            class="sticky top-0 z-40 border-y border-[var(--brand-primary)]/20 bg-[var(--brand-bg)]/92 backdrop-blur-md"
        >
            <div class="mx-auto max-w-6xl px-4">
                <div class="flex flex-wrap items-center gap-3 py-3">
                    <a href="#top" class="flex min-w-0 items-center gap-3">
                        <span
                            v-if="showLogo"
                            class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full ring-1 ring-[var(--brand-primary)]/40"
                        >
                            <img
                                v-if="restaurant.logo"
                                :src="restaurant.logo"
                                :alt="restaurant.name"
                                class="h-full w-full object-cover"
                            />
                            <span
                                v-else
                                class="font-brand-heading text-sm text-[var(--brand-primary)]"
                            >
                                {{ restaurant.name.charAt(0) }}
                            </span>
                        </span>
                        <span
                            class="truncate font-brand-heading text-sm tracking-wide sm:text-base"
                        >
                            {{ restaurant.name }}
                        </span>
                    </a>

                    <label class="relative ms-auto hidden md:block">
                        <span class="sr-only">
                            {{ label({ ar: 'البحث في القائمة', en: 'Search the menu' }) }}
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
                            class="w-56 rounded-full border border-[var(--brand-primary)]/25 bg-[var(--brand-surface)] py-2 ps-9 pe-4 text-sm text-[var(--brand-text)] placeholder:text-[var(--brand-muted)] focus:border-[var(--brand-primary)] focus:outline-none lg:w-64"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>

                    <div
                        class="flex items-center gap-0.5 rounded-full bg-[var(--brand-surface)] p-0.5 ring-1 ring-inset ring-[var(--brand-primary)]/25"
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
                    class="brand-scroll flex items-center gap-1 overflow-x-auto pb-3"
                >
                    <template v-for="(category, index) in categories" :key="category.id">
                        <span
                            v-if="index"
                            class="h-1 w-1 shrink-0 rotate-45 bg-[var(--brand-secondary)]/50"
                            aria-hidden="true"
                        />
                        <button
                            type="button"
                            class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 px-3 py-1.5 text-sm font-medium transition"
                            :class="
                                activeId === category.id
                                    ? 'border-[var(--brand-primary)] text-[var(--brand-primary)]'
                                    : 'border-transparent text-[var(--brand-muted)] hover:text-[var(--brand-text)]'
                            "
                            :aria-current="activeId === category.id ? 'true' : undefined"
                            @click="emit('navigate', category)"
                        >
                            <Icon
                                v-if="category.virtual"
                                name="flame"
                                class="h-3.5 w-3.5"
                            />
                            {{ categoryName(category) }}
                        </button>
                    </template>
                </nav>
            </div>
        </div>

        <main class="mx-auto max-w-6xl px-4 pb-24">
            <template v-if="isSearching">
                <div class="pt-12">
                    <p class="text-sm text-[var(--brand-muted)]" role="status">
                        {{
                            label({
                                ar: `نتائج البحث «${search.trim()}» (${searchResults.length})`,
                                en: `Results for “${search.trim()}” (${searchResults.length})`,
                            })
                        }}
                    </p>

                    <div
                        v-if="searchResults.length"
                        class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <MosaicProductCard
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
                            label({ ar: 'لا توجد نتائج مطابقة', en: 'No matching dishes' })
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
                <div v-if="hasMenu || featured.length" class="mt-14 space-y-20">
                    <section
                        v-if="featured.length"
                        :aria-label="label({ ar: 'أطباق مميزة', en: 'Featured' })"
                    >
                        <div class="mb-8 flex items-center gap-4">
                            <span
                                class="h-2 w-2 rotate-45 bg-[var(--brand-secondary)]"
                                aria-hidden="true"
                            />
                            <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                {{ label({ ar: 'أطباق مميزة', en: 'Featured' }) }}
                            </h2>
                            <span
                                class="h-px flex-1 bg-gradient-to-r from-[var(--brand-primary)]/50 to-transparent"
                                aria-hidden="true"
                            />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <MosaicProductCard
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
                        class="scroll-mt-40 rounded-[var(--brand-radius)] border border-[var(--brand-primary)]/25 bg-[var(--brand-surface)] p-6 sm:p-10"
                    >
                        <div class="mb-8 flex items-center gap-4">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-t-full bg-[var(--brand-primary)] text-[var(--brand-on-primary)]"
                                aria-hidden="true"
                            >
                                <Icon name="flame" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                    {{ categoryName(virtualCategory) }}
                                </h2>
                                <p
                                    v-if="categoryDescription(virtualCategory)"
                                    class="mt-1 text-sm text-[var(--brand-muted)]"
                                >
                                    {{ categoryDescription(virtualCategory) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <MosaicProductCard
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
                        class="scroll-mt-40"
                    >
                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="categoryName(category)"
                            loading="lazy"
                            decoding="async"
                            class="mb-8 h-44 w-full rounded-[var(--brand-radius)] object-cover sm:h-56"
                        />

                        <div class="mb-8 flex items-center gap-4">
                            <span
                                class="font-brand-heading text-2xl tabular-nums text-[var(--brand-secondary)]"
                                aria-hidden="true"
                            >
                                {{ pad(index + sectionStart) }}
                            </span>
                            <div class="min-w-0 flex-1">
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
                            <span
                                class="hidden h-2 w-2 rotate-45 bg-[var(--brand-primary)]/60 sm:block"
                                aria-hidden="true"
                            />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <MosaicProductCard
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

        <footer
            class="border-t border-[var(--brand-primary)]/20 px-5 py-12 text-center"
        >
            <div class="mb-5 flex items-center justify-center gap-2" aria-hidden="true">
                <span class="h-1.5 w-1.5 rotate-45 bg-[var(--brand-secondary)]" />
                <span class="h-1.5 w-1.5 rotate-45 bg-[var(--brand-primary)]" />
                <span class="h-1.5 w-1.5 rotate-45 bg-[var(--brand-secondary)]" />
            </div>
            <p class="font-brand-heading text-xl">{{ restaurant.name }}</p>
            <p class="mt-3 text-xs text-[var(--brand-muted)]">
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
        </footer>
    </div>
</template>
