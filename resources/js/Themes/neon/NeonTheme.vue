<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import NeonProductCard from './NeonProductCard.vue';
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

const contacts = computed(() => {
    const entries = [];

    if (props.restaurant.phone) {
        entries.push({ key: 'phone', icon: 'phone', label: props.restaurant.phone, href: `tel:${props.restaurant.phone}` });
    }

    if (props.restaurant.email) {
        entries.push({ key: 'email', icon: 'mail', label: props.restaurant.email, href: `mailto:${props.restaurant.email}` });
    }

    if (props.restaurant.address) {
        entries.push({ key: 'address', icon: 'pin', label: props.restaurant.address, href: null });
    }

    return entries;
});

const showLogo = computed(() => props.branding.logo_display_mode !== 'hidden');

const label = (value) => (props.locale === 'ar' ? value.ar : value.en);

const pad = (index) => String(index).padStart(2, '0');

const categoryName = (category) =>
    translate(category.name, props.locale, props.restaurant.locale);

const categoryDescription = (category) =>
    translate(category.description, props.locale, props.restaurant.locale);
</script>

<template>
    <div class="bg-[var(--brand-bg)] text-[var(--brand-text)]">
        <header class="relative overflow-hidden px-5 py-16 text-center">
            <div
                class="pointer-events-none absolute inset-0 opacity-70"
                style="background: radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--brand-primary) 45%, transparent) 0%, transparent 60%);"
                aria-hidden="true"
            />

            <div class="relative mx-auto max-w-3xl">
                <span
                    v-if="showLogo"
                    class="mx-auto flex h-24 w-24 items-center justify-center overflow-hidden rounded-[var(--brand-radius)] border border-[var(--brand-secondary)]/50 shadow-[0_0_40px_-8px_var(--brand-secondary)]"
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

                <h1
                    class="mt-6 font-brand-heading text-4xl font-bold uppercase tracking-tight [text-shadow:0_0_26px_var(--brand-primary)] sm:text-6xl lg:text-7xl"
                >
                    {{ restaurant.name }}
                </h1>

                <p
                    class="mt-4 text-[11px] font-semibold uppercase tracking-[0.55em] text-[var(--brand-secondary)]"
                >
                    {{ label({ ar: 'قائمة الليل', en: 'Night Menu' }) }}
                </p>

                <p
                    v-if="description"
                    class="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-[var(--brand-muted)] sm:text-base"
                >
                    {{ description }}
                </p>

                <div
                    v-if="contacts.length"
                    class="mt-8 flex flex-wrap items-center justify-center gap-3 text-xs sm:text-sm"
                >
                    <component
                        :is="contact.href ? 'a' : 'span'"
                        v-for="contact in contacts"
                        :key="contact.key"
                        :href="contact.href"
                        class="inline-flex items-center gap-2 rounded-full border border-[var(--brand-secondary)]/40 bg-[var(--brand-secondary)]/10 px-4 py-2 transition hover:border-[var(--brand-secondary)]"
                    >
                        <Icon :name="contact.icon" class="h-4 w-4 text-[var(--brand-secondary)]" />
                        {{ contact.label }}
                    </component>
                </div>
            </div>
        </header>

        <div
            class="sticky top-0 z-40 border-y border-[var(--brand-primary)]/30 bg-[var(--brand-bg)]/92 backdrop-blur-md"
        >
            <div class="mx-auto max-w-6xl px-4">
                <div class="flex items-center gap-3 py-3">
                    <a href="#top" class="truncate font-brand-heading text-sm font-bold uppercase tracking-widest">
                        {{ restaurant.name }}
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
                            :placeholder="label({ ar: 'ابحث عن طبق...', en: 'Search a dish...' })"
                            class="w-56 rounded-full border border-[var(--brand-primary)]/40 bg-[var(--brand-surface)] py-2 ps-9 pe-4 text-sm text-[var(--brand-text)] placeholder:text-[var(--brand-muted)] focus:border-[var(--brand-primary)] focus:outline-none lg:w-64"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>

                    <div
                        class="flex items-center gap-0.5 rounded-full border border-[var(--brand-primary)]/40 p-0.5"
                        role="group"
                    >
                        <button
                            v-for="option in locales"
                            :key="option.code"
                            type="button"
                            class="rounded-full px-2.5 py-1 text-xs font-bold uppercase transition"
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
                    class="brand-scroll flex gap-2 overflow-x-auto pb-3"
                >
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider transition"
                        :class="
                            activeId === category.id
                                ? 'border-[var(--brand-secondary)] bg-[var(--brand-secondary)]/15 text-[var(--brand-text)] shadow-[0_0_20px_-6px_var(--brand-secondary)]'
                                : 'border-[var(--brand-primary)]/30 text-[var(--brand-muted)] hover:text-[var(--brand-text)]'
                        "
                        :aria-current="activeId === category.id ? 'true' : undefined"
                        @click="emit('navigate', category)"
                    >
                        <Icon v-if="category.virtual" name="flame" class="h-3.5 w-3.5" />
                        {{ categoryName(category) }}
                    </button>
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

                    <div v-if="searchResults.length" class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <NeonProductCard
                            v-for="item in searchResults"
                            :key="item.id"
                            :item="item"
                            :locale="locale"
                            :currency="currency"
                            @open="emit('open', $event)"
                        />
                    </div>

                    <MenuEmpty
                        v-else
                        class="mt-8"
                        icon="search"
                        :title="label({ ar: 'لا توجد نتائج مطابقة', en: 'No matching dishes' })"
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
                        <div class="mb-6 flex items-end gap-4">
                            <h2
                                class="font-brand-heading text-2xl font-bold uppercase tracking-tight [text-shadow:0_0_18px_var(--brand-primary)] sm:text-3xl"
                            >
                                {{ label({ ar: 'مميز الليلة', en: "Tonight's Picks" }) }}
                            </h2>
                            <span class="h-px flex-1 bg-gradient-to-r from-[var(--brand-primary)] to-transparent" aria-hidden="true" />
                        </div>

                        <div class="brand-scroll flex snap-x snap-mandatory gap-5 overflow-x-auto pb-4">
                            <div v-for="item in featured" :key="item.id" class="w-64 shrink-0 snap-start">
                                <NeonProductCard
                                    :item="item"
                                    :locale="locale"
                                    :currency="currency"
                                    @open="emit('open', $event)"
                                />
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="virtualCategory"
                        :id="categoryAnchor(virtualCategory)"
                        class="scroll-mt-40 rounded-[var(--brand-radius)] border border-[var(--brand-primary)]/40 bg-[var(--brand-surface)] p-6 shadow-[0_0_50px_-24px_var(--brand-primary)] sm:p-10"
                    >
                        <div class="mb-6 flex items-center gap-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--brand-primary)] text-[var(--brand-on-primary)] shadow-[0_0_24px_-4px_var(--brand-primary)]" aria-hidden="true">
                                <Icon name="flame" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h2 class="font-brand-heading text-2xl font-bold uppercase tracking-tight sm:text-3xl">
                                    {{ categoryName(virtualCategory) }}
                                </h2>
                                <p v-if="categoryDescription(virtualCategory)" class="mt-1 text-sm text-[var(--brand-muted)]">
                                    {{ categoryDescription(virtualCategory) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <NeonProductCard
                                v-for="item in virtualCategory.items"
                                :key="item.id"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
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
                        <div class="mb-6 flex items-end gap-4">
                            <span
                                class="font-brand-heading text-2xl font-bold tabular-nums text-[var(--brand-secondary)] [text-shadow:0_0_18px_var(--brand-secondary)]"
                                aria-hidden="true"
                            >
                                {{ pad(index + 1) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <h2 class="font-brand-heading text-2xl font-bold uppercase tracking-tight sm:text-3xl">
                                    {{ categoryName(category) }}
                                </h2>
                                <p v-if="categoryDescription(category)" class="mt-1 max-w-2xl text-sm text-[var(--brand-muted)]">
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
                            class="mb-6 h-44 w-full rounded-[var(--brand-radius)] object-cover sm:h-56"
                        />

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <NeonProductCard
                                v-for="item in category.items"
                                :key="item.id"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
                                @open="emit('open', $event)"
                            />
                        </div>
                    </section>
                </div>

                <MenuEmpty
                    v-else
                    class="mt-12"
                    icon="clock"
                    :title="label({ ar: 'القائمة قيد الإعداد', en: 'The menu is being prepared' })"
                    :description="
                        label({
                            ar: 'لم تتم إضافة أي أصناف بعد. يرجى العودة قريباً.',
                            en: 'No dishes have been added yet. Please check back soon.',
                        })
                    "
                />
            </template>
        </main>

        <footer class="border-t border-[var(--brand-primary)]/30 px-5 py-12 text-center">
            <p class="font-brand-heading text-xl font-bold uppercase tracking-widest [text-shadow:0_0_18px_var(--brand-primary)]">
                {{ restaurant.name }}
            </p>
            <p class="mt-3 text-xs text-[var(--brand-muted)]">
                {{
                    label({
                        ar: 'هذه القائمة للعرض فقط — لا تتضمن طلباً أو دفعاً إلكترونياً.',
                        en: 'This menu is for display only — no ordering or online payment.',
                    })
                }}
            </p>
            <p class="mt-6 text-[11px] uppercase tracking-[0.3em] text-[var(--brand-muted)]">
                {{ new Date().getFullYear() }}
            </p>
        </footer>
    </div>
</template>
