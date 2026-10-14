<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import PulseProductCard from './PulseProductCard.vue';
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
        case 'rounded':
            return 'rounded-2xl';
        default:
            return 'rounded-xl';
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
        <header class="px-4 pt-8 sm:px-6">
            <div class="mx-auto max-w-6xl">
                <div class="flex items-center gap-3">
                    <span
                        v-if="showLogo && restaurant.logo"
                        class="inline-flex h-12 w-12 items-center justify-center overflow-hidden ring-2 ring-[var(--brand-primary)]"
                        :class="logoShape"
                    >
                        <img
                            :src="restaurant.logo"
                            :alt="restaurant.name"
                            class="h-full w-full object-cover"
                        />
                    </span>
                    <span
                        v-else-if="showLogo"
                        class="inline-flex h-12 w-12 items-center justify-center bg-[var(--brand-primary)] font-brand-heading text-xl font-bold text-[var(--brand-on-primary)]"
                        :class="logoShape"
                    >
                        {{ restaurant.name.charAt(0) }}
                    </span>

                    <span
                        class="rounded-full bg-[var(--brand-primary)]/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--brand-primary)]"
                    >
                        {{ label({ ar: 'القائمة الرقمية', en: 'Digital Menu' }) }}
                    </span>
                </div>

                <h1
                    class="mt-6 max-w-3xl font-brand-heading text-5xl font-black leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl"
                >
                    {{ restaurant.name }}
                </h1>

                <p
                    v-if="description"
                    class="mt-5 max-w-2xl text-sm leading-relaxed text-[var(--brand-muted)] sm:text-base"
                >
                    {{ description }}
                </p>

                <div
                    v-if="hasContact"
                    class="mt-6 flex flex-wrap items-center gap-2 text-sm"
                >
                    <a
                        v-if="restaurant.phone"
                        :href="`tel:${restaurant.phone}`"
                        dir="ltr"
                        class="inline-flex items-center gap-2 rounded-full bg-[var(--brand-secondary)] px-4 py-2 font-semibold text-white transition hover:opacity-90"
                    >
                        <Icon name="phone" class="h-4 w-4" />
                        {{ restaurant.phone }}
                    </a>
                    <a
                        v-if="restaurant.email"
                        :href="`mailto:${restaurant.email}`"
                        dir="ltr"
                        class="inline-flex items-center gap-2 rounded-full border-2 border-[var(--brand-border)] px-4 py-2 font-semibold transition hover:border-[var(--brand-primary)]"
                    >
                        <Icon name="mail" class="h-4 w-4" />
                        {{ restaurant.email }}
                    </a>
                    <span
                        v-if="restaurant.address"
                        class="inline-flex items-center gap-2 rounded-full border-2 border-[var(--brand-border)] px-4 py-2 font-semibold text-[var(--brand-muted)]"
                    >
                        <Icon name="pin" class="h-4 w-4" />
                        {{ restaurant.address }}
                    </span>
                </div>

                <div
                    class="relative mt-8 aspect-[16/9] overflow-hidden rounded-[var(--brand-radius)] bg-[var(--brand-border)]/40 sm:aspect-[21/9]"
                >
                    <img
                        v-if="restaurant.cover_image"
                        :src="restaurant.cover_image"
                        :alt="restaurant.name"
                        class="h-full w-full animate-ken-burns object-cover motion-reduce:animate-none"
                        fetchpriority="high"
                    />
                    <div
                        v-else
                        class="h-full w-full bg-gradient-to-br from-[var(--brand-primary)] via-[var(--brand-secondary)] to-[var(--brand-primary)]"
                    />
                    <span
                        class="absolute inset-x-0 bottom-0 h-2 bg-[var(--brand-accent)]"
                        aria-hidden="true"
                    />
                </div>
            </div>
        </header>

        <div
            class="sticky top-0 z-40 mt-8 border-b-2 border-[var(--brand-border)] bg-[var(--brand-bg)]/95 backdrop-blur-md"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex items-center gap-3 py-3">
                    <label class="relative min-w-0 flex-1">
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
                            class="pointer-events-none absolute inset-y-0 start-4 my-auto h-5 w-5 text-[var(--brand-muted)]"
                        />
                        <input
                            type="search"
                            :value="search"
                            :placeholder="
                                label({ ar: 'ابحث عن طبق...', en: 'Search a dish...' })
                            "
                            class="w-full rounded-full border-2 border-[var(--brand-border)] bg-[var(--brand-surface)] py-2.5 ps-12 pe-4 text-sm font-medium placeholder:text-[var(--brand-muted)] focus:border-[var(--brand-primary)] focus:outline-none"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>

                    <div
                        class="flex shrink-0 items-center gap-0.5 rounded-full border-2 border-[var(--brand-border)] p-0.5"
                        role="group"
                    >
                        <button
                            v-for="option in locales"
                            :key="option.code"
                            type="button"
                            class="rounded-full px-3 py-1.5 text-xs font-bold transition"
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
                        class="relative shrink-0 whitespace-nowrap py-3 text-sm font-bold uppercase tracking-wide transition"
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
                            class="absolute inset-x-0 bottom-0 h-1 rounded-full bg-[var(--brand-primary)]"
                            aria-hidden="true"
                        />
                    </button>
                </nav>
            </div>
        </div>

        <main class="mx-auto max-w-6xl px-4 pb-24 sm:px-6">
            <template v-if="isSearching">
                <div class="pt-12">
                    <h2 class="font-brand-heading text-3xl font-black">
                        {{
                            label({
                                ar: `نتائج «${search.trim()}»`,
                                en: `Results for “${search.trim()}”`,
                            })
                        }}
                    </h2>
                    <p class="mt-1 text-sm font-medium text-[var(--brand-muted)]" role="status">
                        {{ searchResults.length }}
                        {{ label({ ar: 'نتيجة', en: 'results' }) }}
                    </p>

                    <div
                        v-if="searchResults.length"
                        class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <PulseProductCard
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
                        <div class="mb-6 flex items-center gap-3">
                            <span
                                class="flex h-8 items-center rounded-full bg-[var(--brand-accent)] px-3 text-xs font-black uppercase tracking-wide text-[var(--brand-secondary)]"
                            >
                                {{ label({ ar: 'مميز', en: 'Featured' }) }}
                            </span>
                            <span class="h-1 flex-1 rounded-full bg-[var(--brand-border)]" />
                        </div>

                        <div
                            class="brand-scroll -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0"
                        >
                            <div
                                v-for="item in featured"
                                :key="item.id"
                                class="w-72 shrink-0 snap-start sm:w-80"
                            >
                                <PulseProductCard
                                    :item="item"
                                    :locale="locale"
                                    :currency="currency"
                                    :card-style="cardStyle"
                                    @open="emit('open', $event)"
                                />
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="virtualCategory"
                        :id="categoryAnchor(virtualCategory)"
                        class="scroll-mt-36"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-[var(--brand-primary)] font-brand-heading text-sm font-black text-[var(--brand-on-primary)]"
                            >
                                <Icon name="flame" class="h-5 w-5" />
                            </span>
                            <h2
                                class="font-brand-heading text-2xl font-black uppercase tracking-tight sm:text-3xl"
                            >
                                {{ categoryName(virtualCategory) }}
                            </h2>
                        </div>

                        <img
                            v-if="virtualCategory.image"
                            :src="virtualCategory.image"
                            :alt="categoryName(virtualCategory)"
                            class="mb-6 h-48 w-full rounded-[var(--brand-radius)] object-cover"
                        />

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <PulseProductCard
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
                        <div class="mb-6 flex items-center gap-3">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-[var(--brand-primary)] font-brand-heading text-sm font-black text-[var(--brand-primary)] tabular-nums"
                                aria-hidden="true"
                            >
                                {{ String(index + sectionStart).padStart(2, '0') }}
                            </span>
                            <div>
                                <h2
                                    class="font-brand-heading text-2xl font-black uppercase tracking-tight sm:text-3xl"
                                >
                                    {{ categoryName(category) }}
                                </h2>
                                <p
                                    v-if="categoryDescription(category)"
                                    class="mt-0.5 text-sm text-[var(--brand-muted)]"
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

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <PulseProductCard
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

        <footer class="bg-[var(--brand-secondary)] px-5 py-14 text-white">
            <div class="mx-auto max-w-6xl">
                <p class="font-brand-heading text-3xl font-black tracking-tight">
                    {{ restaurant.name }}
                </p>
                <span
                    class="mt-4 block h-1.5 w-20 rounded-full bg-[var(--brand-primary)]"
                    aria-hidden="true"
                />
                <p class="mt-4 text-xs text-white/70">
                    {{
                        label({
                            ar: 'هذه القائمة للعرض فقط — لا تتضمن طلباً أو دفعاً إلكترونياً.',
                            en: 'This menu is for display only — no ordering or online payment.',
                        })
                    }}
                </p>
                <p class="mt-6 text-[11px] uppercase tracking-[0.3em] text-white/50">
                    {{ new Date().getFullYear() }}
                </p>
            </div>
        </footer>
    </div>
</template>
