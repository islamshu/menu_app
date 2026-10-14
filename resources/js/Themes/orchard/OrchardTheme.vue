<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuEmpty from '@/Components/Menu/MenuEmpty.vue';
import OrchardProductCard from './OrchardProductCard.vue';
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

const categoryName = (category) =>
    translate(category.name, props.locale, props.restaurant.locale);

const categoryDescription = (category) =>
    translate(category.description, props.locale, props.restaurant.locale);
</script>

<template>
    <div class="bg-[var(--brand-bg)] text-[var(--brand-text)]">
        <header class="relative overflow-hidden px-5 py-16">
            <div
                class="pointer-events-none absolute -top-24 -end-24 h-72 w-72 rounded-full bg-[var(--brand-accent)]/40 blur-3xl"
                aria-hidden="true"
            />
            <div
                class="pointer-events-none absolute -top-16 -start-16 h-56 w-56 rounded-full bg-[var(--brand-secondary)]/20 blur-3xl"
                aria-hidden="true"
            />

            <div class="relative mx-auto max-w-3xl text-center">
                <span
                    v-if="showLogo"
                    class="mx-auto flex h-28 w-28 items-center justify-center overflow-hidden rounded-[2.5rem] border-4 border-[var(--brand-bg)] bg-[var(--brand-surface)] shadow-[0_24px_60px_-30px_rgba(0,0,0,0.4)]"
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

                <p
                    class="mt-6 inline-flex items-center gap-2 rounded-full bg-[var(--brand-primary)]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand-primary)]"
                >
                    <Icon name="sparkles" class="h-3.5 w-3.5" />
                    {{ label({ ar: 'طازج يومياً', en: 'Fresh Daily' }) }}
                </p>

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
                    v-if="contacts.length"
                    class="mt-8 flex flex-wrap items-center justify-center gap-3 text-xs sm:text-sm"
                >
                    <component
                        :is="contact.href ? 'a' : 'span'"
                        v-for="contact in contacts"
                        :key="contact.key"
                        :href="contact.href"
                        class="inline-flex items-center gap-2 rounded-full bg-[var(--brand-surface)] px-4 py-2 shadow-[0_10px_30px_-18px_rgba(0,0,0,0.5)] transition hover:text-[var(--brand-primary)]"
                    >
                        <Icon :name="contact.icon" class="h-4 w-4 text-[var(--brand-secondary)]" />
                        {{ contact.label }}
                    </component>
                </div>
            </div>
        </header>

        <div
            class="sticky top-0 z-40 border-b border-[var(--brand-border)] bg-[var(--brand-bg)]/92 backdrop-blur-md"
        >
            <div class="mx-auto max-w-6xl px-4">
                <div class="flex items-center gap-3 py-3">
                    <a href="#top" class="truncate font-brand-heading text-sm sm:text-base">
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
                            class="w-56 rounded-full border border-[var(--brand-border)] bg-[var(--brand-surface)] py-2 ps-9 pe-4 text-sm text-[var(--brand-text)] placeholder:text-[var(--brand-muted)] focus:border-[var(--brand-primary)] focus:outline-none lg:w-64"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>

                    <div
                        class="flex items-center gap-0.5 rounded-full bg-[var(--brand-surface)] p-0.5"
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
                    class="brand-scroll flex gap-2 overflow-x-auto pb-3"
                >
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-4 py-1.5 text-sm font-medium transition"
                        :class="
                            activeId === category.id
                                ? 'border-transparent bg-[var(--brand-primary)] text-[var(--brand-on-primary)]'
                                : 'border-[var(--brand-border)] text-[var(--brand-muted)] hover:border-[var(--brand-primary)] hover:text-[var(--brand-text)]'
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

                    <div v-if="searchResults.length" class="mt-8 columns-2 gap-5 lg:columns-3">
                        <OrchardProductCard
                            v-for="item in searchResults"
                            :key="item.id"
                            class="mb-5"
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
                        <div class="mb-8 flex items-center gap-3">
                            <Icon name="flame" class="h-6 w-6 text-[var(--brand-secondary)]" />
                            <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                {{ label({ ar: 'أطباق مميزة', en: 'Featured' }) }}
                            </h2>
                        </div>

                        <div class="columns-2 gap-5 lg:columns-3">
                            <OrchardProductCard
                                v-for="item in featured"
                                :key="item.id"
                                class="mb-5"
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
                        class="scroll-mt-40"
                    >
                        <div class="mb-8 flex items-center gap-3">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--brand-primary)] text-[var(--brand-on-primary)]"
                                aria-hidden="true"
                            >
                                <Icon name="flame" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                    {{ categoryName(virtualCategory) }}
                                </h2>
                                <p v-if="categoryDescription(virtualCategory)" class="mt-1 text-sm text-[var(--brand-muted)]">
                                    {{ categoryDescription(virtualCategory) }}
                                </p>
                            </div>
                        </div>

                        <div class="columns-2 gap-5 lg:columns-3">
                            <OrchardProductCard
                                v-for="item in virtualCategory.items"
                                :key="item.id"
                                class="mb-5"
                                :item="item"
                                :locale="locale"
                                :currency="currency"
                                :card-style="cardStyle"
                                @open="emit('open', $event)"
                            />
                        </div>
                    </section>

                    <section
                        v-for="category in regularCategories"
                        :key="category.id"
                        :id="categoryAnchor(category)"
                        class="scroll-mt-40"
                    >
                        <div class="mb-6 flex flex-wrap items-center gap-3">
                            <h2 class="font-brand-heading text-2xl sm:text-3xl">
                                {{ categoryName(category) }}
                            </h2>
                            <span class="h-px flex-1 bg-[var(--brand-border)]" aria-hidden="true" />
                        </div>
                        <p
                            v-if="categoryDescription(category)"
                            class="mb-6 max-w-2xl text-sm text-[var(--brand-muted)]"
                        >
                            {{ categoryDescription(category) }}
                        </p>

                        <img
                            v-if="category.image"
                            :src="category.image"
                            :alt="categoryName(category)"
                            loading="lazy"
                            decoding="async"
                            class="mb-6 h-44 w-full rounded-[var(--brand-radius)] object-cover sm:h-56"
                        />

                        <div class="columns-2 gap-5 lg:columns-3">
                            <OrchardProductCard
                                v-for="item in category.items"
                                :key="item.id"
                                class="mb-5"
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

        <footer class="border-t border-[var(--brand-border)] px-5 py-12 text-center">
            <p class="font-brand-heading text-xl">{{ restaurant.name }}</p>
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
