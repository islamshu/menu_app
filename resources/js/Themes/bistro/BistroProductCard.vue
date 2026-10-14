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
            class="group flex w-full items-start gap-4 py-4 text-start transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--brand-bg)]"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <span
                v-if="item.image"
                class="hidden h-14 w-14 shrink-0 overflow-hidden rounded-full border border-[var(--brand-secondary)]/50 sm:block"
            >
                <img
                    :src="item.image"
                    :alt="name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-105"
                />
            </span>
            <span
                v-else
                class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-full border border-[var(--brand-secondary)]/40 sm:flex"
                aria-hidden="true"
            >
                <span class="font-brand-heading text-lg text-[var(--brand-primary)]">
                    {{ initial }}
                </span>
            </span>

            <span class="min-w-0 flex-1">
                <span class="flex items-baseline gap-3">
                    <span class="font-brand-heading text-lg leading-snug">
                        {{ name }}
                    </span>
                    <span
                        class="h-px flex-1 border-b border-dotted border-[var(--brand-secondary)]/70"
                        aria-hidden="true"
                    />
                    <span class="shrink-0 text-end">
                        <span class="font-brand-heading text-lg text-[var(--brand-primary)]">
                            {{ formatMoney(item.price, currency, locale) }}
                        </span>
                        <span
                            v-if="hasDiscount(item)"
                            class="block text-[11px] text-[var(--brand-muted)] line-through"
                        >
                            {{ formatMoney(item.compare_at_price, currency, locale) }}
                        </span>
                    </span>
                </span>

                <span
                    v-if="description"
                    class="mt-1 block text-sm leading-relaxed text-[var(--brand-muted)]"
                >
                    {{ description }}
                </span>

                <span
                    v-if="item.is_featured || item.is_popular || item.is_new"
                    class="mt-2 flex flex-wrap gap-1.5"
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
                </span>
            </span>
        </button>
    </article>
</template>
