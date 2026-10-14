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
        default: 'flat',
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
        case 'elevated':
            return 'border border-transparent bg-[var(--brand-surface)] shadow-[0_22px_45px_-38px_rgba(20,22,29,0.55)]';
        case 'outline':
            return 'border-2 border-[var(--brand-border)] bg-transparent';
        case 'solid':
            return 'border border-transparent bg-[var(--brand-primary)] text-[var(--brand-on-primary)]';
        default:
            return 'border border-[var(--brand-border)] bg-[var(--brand-surface)]';
    }
});
</script>

<template>
    <article>
        <button
            type="button"
            class="group flex h-full w-full flex-col overflow-hidden rounded-[var(--brand-radius)] text-start transition duration-300 hover:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--brand-bg)]"
            :class="surface"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <div
                class="relative aspect-[5/4] overflow-hidden bg-[var(--brand-border)]/40"
            >
                <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--brand-primary)]/15 to-[var(--brand-accent)]/25"
                    aria-hidden="true"
                >
                    <span
                        class="font-brand-heading text-6xl font-bold text-[var(--brand-primary)]/60"
                    >
                        {{ initial }}
                    </span>
                </div>

                <span
                    class="absolute bottom-3 end-3 rounded-full bg-[var(--brand-primary)] px-3.5 py-1.5 text-sm font-bold text-[var(--brand-on-primary)] shadow-sm"
                >
                    {{ formatMoney(item.price, currency, locale) }}
                </span>

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

            <div class="flex flex-1 flex-col gap-1.5 p-4">
                <h3
                    class="font-brand-heading text-lg font-bold leading-snug"
                >
                    {{ name }}
                </h3>

                <p
                    v-if="description"
                    class="line-clamp-2 text-sm leading-relaxed text-[var(--brand-muted)]"
                >
                    {{ description }}
                </p>

                <span
                    v-if="hasDiscount(item)"
                    class="mt-auto pt-2 text-xs font-medium text-[var(--brand-muted)] line-through"
                >
                    {{
                        formatMoney(item.compare_at_price, currency, locale)
                    }}
                </span>
            </div>
        </button>
    </article>
</template>
