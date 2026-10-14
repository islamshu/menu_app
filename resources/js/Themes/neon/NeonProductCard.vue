<script setup>
import { computed } from 'vue';
import MenuBadge from '@/Components/Menu/MenuBadge.vue';
import { formatMoney, hasDiscount, translate } from '@/Support/menu';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    locale: {
        type: String,
        required: true,
    },
    currency: {
        type: String,
        required: true,
    },
});

defineEmits(['open']);

const name = computed(() => translate(props.item.name, props.locale));
const description = computed(() =>
    translate(props.item.description, props.locale),
);
const initial = computed(() => name.value.charAt(0));
</script>

<template>
    <article>
        <button
            type="button"
            class="group flex h-full w-full flex-col overflow-hidden rounded-[var(--brand-radius)] border border-[var(--brand-primary)]/40 bg-[var(--brand-surface)] text-start transition duration-300 hover:-translate-y-1 hover:border-[var(--brand-primary)] hover:shadow-[0_0_36px_-8px_var(--brand-primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-secondary)]"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <div class="relative aspect-square overflow-hidden">
                <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover opacity-90 transition duration-700 ease-out group-hover:scale-105 group-hover:opacity-100"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--brand-primary)]/30 via-[var(--brand-bg)] to-[var(--brand-secondary)]/30"
                    aria-hidden="true"
                >
                    <span class="font-brand-heading text-5xl font-bold text-[var(--brand-primary)] [text-shadow:0_0_20px_var(--brand-primary)]">
                        {{ initial }}
                    </span>
                </div>

                <div
                    v-if="item.is_featured || item.is_popular || item.is_new"
                    class="absolute start-3 top-3 flex flex-wrap gap-1.5"
                >
                    <MenuBadge
                        v-if="item.is_featured"
                        variant="featured"
                        :label="locale === 'ar' ? 'مميز' : 'Featured'"
                    />
                    <MenuBadge
                        v-if="item.is_popular"
                        variant="popular"
                        :label="locale === 'ar' ? 'الأكثر طلباً' : 'Popular'"
                    />
                    <MenuBadge
                        v-if="item.is_new"
                        variant="new"
                        :label="locale === 'ar' ? 'جديد' : 'New'"
                    />
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-2 p-4">
                <h3 class="font-brand-heading text-base font-bold uppercase tracking-wide">
                    {{ name }}
                </h3>

                <p
                    v-if="description"
                    class="line-clamp-2 text-sm leading-relaxed text-[var(--brand-muted)]"
                >
                    {{ description }}
                </p>

                <div class="mt-auto flex items-center justify-between gap-3 pt-2">
                    <span class="rounded-full bg-[var(--brand-primary)] px-3.5 py-1 text-sm font-bold text-[var(--brand-on-primary)] shadow-[0_0_20px_-4px_var(--brand-primary)]">
                        {{ formatMoney(item.price, currency, locale) }}
                    </span>
                    <span
                        v-if="hasDiscount(item)"
                        class="text-xs text-[var(--brand-muted)] line-through"
                    >
                        {{ formatMoney(item.compare_at_price, currency, locale) }}
                    </span>
                </div>
            </div>
        </button>
    </article>
</template>
