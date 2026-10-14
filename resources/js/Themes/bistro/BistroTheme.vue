<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import BistroProductCard from './BistroProductCard.vue';
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
        entries.push({ key: 'phone', label: props.restaurant.phone, href: `tel:${props.restaurant.phone}` });
    }

    if (props.restaurant.email) {
        entries.push({ key: 'email', label: props.restaurant.email, href: `mailto:${props.restaurant.email}` });
    }

    if (props.restaurant.address) {
        entries.push({ key: 'address', label: props.restaurant.address, href: null });
    }

    return entries;
});

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
        <header class="border-b-4 border-double border-[var(--brand-secondary)]/60 px-5 py-14">
            <div class="mx-auto max-w-3xl text-center">
                <span
                    v-if="showLogo"
                    class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border border-[var(--brand-secondary)]/60 bg-[var(--brand-surface)]"
                >
                    <img
                        v-if="restaurant.logo"
                        :src="restaurant.logo"
                        :alt="restaurant.name"
                        class="h-full w-full object-cover"
                    />
                    <span
                        v-else
                        class="font-brand-heading text-3xl text-[var(--brand-primary)]"
                    >
                        {{ restaurant.name.charAt(0) }}
                    </span>
                </span>

                <h1
                    class="mt-6 font-brand-heading text-4xl uppercase tracking-[0.12em] sm:text-5xl lg:text-6xl"
                >
                    {{ restaurant.name }}
                </h1>

                <div class="mt-5 flex items-center justify-center gap-3">
                    <span class="h-px w-12 bg-[var(--brand-secondary)]" aria-hidden="true" />
                    <span class="h-1.5 w-1.5 rotate-45 border border-[var(--brand-secondary)]" aria-hidden="true" />
                    <span class="h-px w-12 bg-[var(--brand-secondary)]" aria-hidden="true" />
                </div>

                <p
                    v-if="description"
                    class="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-[var(--brand-muted)] sm:text-base"
                >
                    {{ description }}
                </p>

                <div
                    v-if="contacts.length"
                    class="mt-7 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs uppercase tracking-[0.2em] text-[var(--brand-muted)] sm:text-sm"
                >
                    <template v-for="(contact, index) in contacts" :key="contact.key">
                        <span v-if="index" class="text-[var(--brand-secondary)]" aria-hidden="true">•</span>
                        <component
                            :is="contact.href ? 'a' : 'span'"
                            :href="contact.href"
                            class="transition hover:text-[var(--brand-primary)]"
                        >
                            {{ contact.label }}
                        </component>
                    </template>
                </div>
            </div>
        </header>

        <div
            class="sticky top-0 z-40 border-b border-[var(--brand-secondary)]/40 bg-[var(--brand-bg)]/95 backdrop-blur-md"
        >
            <div class="mx-auto max-w-5xl px-4">
                <div class="flex items-center gap-3 py-3">
                    <a href="#top" class="truncate font-brand-heading text-sm uppercase tracking-[0.2em]">
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
                            class="border-b border-[var(--brand-secondary)]/50 bg-transparent py-1.5 ps-8 pe-3 text-sm text-[var(--brand-text)] placeholder:text-[var(--brand-muted)] focus:border-[var(--brand-primary)] focus:outline-none"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>

                    <div class="flex items-center gap-3" role="group">
                        <button
                            v-for="option in locales"
                            :key="option.code"
                            type="button"
                            class="text-xs font-semibold uppercase tracking-widest transition"
                            :class="
                                locale === option.code
                                    ? 'text-[var(--brand-primary)]'
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
                    class="brand-scroll flex gap-6 overflow-x-auto pb-0 md:justify-center"
                >
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap border-b-2 pb-3 pt-1 text-xs font-medium uppercase tracking-[0.2em] transition"
                        :class="
                            activeId === category.id
                                ? 'border-[var(--brand-primary)] text-[var(--brand-text)]'
                                : 'border-transparent text-[var(--brand-muted)] hover:text-[var(--brand-text)]'
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

        <main class="mx-auto max-w-5xl px-4 pb-24">
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

                    <div v-if="searchResults.length" class="mt-6 grid gap-x-16 md:grid-cols-2">
                        <BistroProductCard
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
                <div v-if="hasMenu || featured.length" class="mt-14 space-y-16">
                    <section
                        v-if="featured.length"
                        :aria-label="label({ ar: 'أطباق مميزة', en: 'Featured' })"
                    >
                        <div class="mb-6 flex items-center justify-center gap-4">
                            <span class="h-px w-16 bg-[var(--brand-secondary)]" aria-hidden="true" />
                            <h2 class="font-brand-heading text-xl uppercase tracking-[0.25em] sm:text-2xl">
                                {{ label({ ar: 'أطباق مميزة', en: 'Featured' }) }}
                            </h2>
                            <span class="h-px w-16 bg-[var(--brand-secondary)]" aria-hidden="true" />
                        </div>

                        <div class="grid gap-x-16 md:grid-cols-2">
                            <BistroProductCard
                                v-for="item in featured"
                                :key="item.id"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
                                @open="emit('open', $event)"
                            />
                        </div>
                    </section>

                    <section
                        v-if="virtualCategory"
                        :id="categoryAnchor(virtualCategory)"
                        class="scroll-mt-40 rounded-[var(--brand-radius)] border border-double border-2 border-[var(--brand-secondary)]/50 bg-[var(--brand-surface)] p-6 sm:p-10"
                    >
                        <div class="mb-6 flex items-center justify-center gap-4">
                            <span class="h-px w-16 bg-[var(--brand-secondary)]" aria-hidden="true" />
                            <h2 class="font-brand-heading text-xl uppercase tracking-[0.25em] sm:text-2xl">
                                {{ categoryName(virtualCategory) }}
                            </h2>
                            <span class="h-px w-16 bg-[var(--brand-secondary)]" aria-hidden="true" />
                        </div>
                        <p
                            v-if="categoryDescription(virtualCategory)"
                            class="mb-4 text-center text-sm text-[var(--brand-muted)]"
                        >
                            {{ categoryDescription(virtualCategory) }}
                        </p>

                        <div class="grid gap-x-16 md:grid-cols-2">
                            <BistroProductCard
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
                        <div class="mb-6 flex items-center justify-center gap-4">
                            <span class="font-brand-heading text-lg text-[var(--brand-secondary)]" aria-hidden="true">
                                {{ pad(index + sectionStart) }}
                            </span>
                            <h2 class="font-brand-heading text-xl uppercase tracking-[0.25em] sm:text-2xl">
                                {{ categoryName(category) }}
                            </h2>
                            <span class="font-brand-heading text-lg text-[var(--brand-secondary)]" aria-hidden="true">
                                {{ pad(index + sectionStart) }}
                            </span>
                        </div>
                        <p
                            v-if="categoryDescription(category)"
                            class="mx-auto mb-4 max-w-2xl text-center text-sm text-[var(--brand-muted)]"
                        >
                            {{ categoryDescription(category) }}
                        </p>

                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="categoryName(category)"
                            loading="lazy"
                            decoding="async"
                            class="mx-auto mb-8 h-44 w-full max-w-3xl rounded-[var(--brand-radius)] object-cover sm:h-56"
                        />

                        <div class="grid gap-x-16 md:grid-cols-2">
                            <BistroProductCard
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

        <footer class="border-t-4 border-double border-[var(--brand-secondary)]/60 px-5 py-12 text-center">
            <p class="font-brand-heading text-xl uppercase tracking-[0.2em]">{{ restaurant.name }}</p>
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
