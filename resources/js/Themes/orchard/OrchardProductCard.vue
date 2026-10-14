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
    cardStyle: {
        type: String,
        default: 'elevated',
    },
});

defineEmits(['open']);

const name = computed(() => translate(props.item.name, props.locale));
const description = computed(() =>
    translate(props.item.description, props.locale),
);
const initial = computed(() => name.value.charAt(0));

const surface = computed(() => {
    switch (props.cardStyle) {
        case 'outline':
            return 'border border-[var(--brand-border)]';
        case 'flat':
            return '';
        case 'solid':
            return 'bg-[var(--brand-primary)] text-[var(--brand-on-primary)]';
        default:
            return 'shadow-[0_26px_50px_-32px_rgba(0,0,0,0.45)]';
    }
});
</script>

<template>
    <article class="break-inside-avoid">
        <button
            type="button"
            class="group block w-full overflow-hidden rounded-[var(--brand-radius)] bg-[var(--brand-surface)] text-start transition duration-300 hover:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--brand-bg)]"
            :class="surface"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <div class="relative">
                <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="name"
                    loading="lazy"
                    decoding="async"
                    class="aspect-[4/5] w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                />
                <div
                    v-else
                    class="flex aspect-[4/5] w-full items-center justify-center bg-gradient-to-br from-[var(--brand-accent)]/60 to-[var(--brand-secondary)]/30"
                    aria-hidden="true"
                >
                    <span class="font-brand-heading text-5xl text-[var(--brand-primary)]">
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

                <span
                    class="absolute bottom-3 end-3 rounded-full bg-[var(--brand-secondary)] px-3 py-1 text-sm font-semibold text-white shadow-[0_10px_24px_-12px_rgba(0,0,0,0.7)]"
                >
                    {{ formatMoney(item.price, currency, locale) }}
                </span>
            </div>

            <div class="p-4">
                <h3 class="font-brand-heading text-lg leading-snug">
                    {{ name }}
                </h3>

                <p
                    v-if="description"
                    class="mt-1 line-clamp-2 text-sm leading-relaxed text-[var(--brand-muted)]"
                >
                    {{ description }}
                </p>

                <span
                    v-if="hasDiscount(item)"
                    class="mt-2 block text-xs text-[var(--brand-muted)] line-through"
                >
                    {{ formatMoney(item.compare_at_price, currency, locale) }}
                </span>
            </div>
        </button>
    </article>
</template>
