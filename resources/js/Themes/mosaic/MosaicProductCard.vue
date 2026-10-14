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
        default: 'outline',
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
            return 'bg-[var(--brand-surface)] shadow-[0_24px_50px_-38px_rgba(0,0,0,0.8)]';
        case 'flat':
            return 'bg-[var(--brand-surface)]/60';
        case 'solid':
            return 'bg-[var(--brand-primary)] text-[var(--brand-on-primary)]';
        default:
            return 'bg-[var(--brand-surface)] ring-1 ring-inset ring-[var(--brand-primary)]/30';
    }
});
</script>

<template>
    <article>
        <button
            type="button"
            class="group flex w-full flex-col overflow-hidden rounded-[var(--brand-radius)] text-start transition duration-300 hover:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-secondary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--brand-bg)]"
            :class="surface"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <div class="p-3 pb-0">
                <div
                    class="relative aspect-[4/5] overflow-hidden rounded-t-full rounded-b-[var(--brand-radius)] ring-1 ring-inset ring-[var(--brand-primary)]/25"
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
                        class="flex h-full w-full items-center justify-center bg-gradient-to-b from-[var(--brand-primary)]/25 to-[var(--brand-secondary)]/25"
                        aria-hidden="true"
                    >
                        <span class="font-brand-heading text-5xl text-[var(--brand-primary)]">
                            {{ initial }}
                        </span>
                    </div>

                    <div
                        v-if="item.is_featured || item.is_popular || item.is_new"
                        class="absolute inset-x-3 top-8 flex flex-wrap justify-center gap-1.5"
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
            </div>

            <div class="flex flex-1 flex-col gap-2 p-5">
                <h3 class="font-brand-heading text-lg leading-snug">
                    {{ name }}
                </h3>

                <p
                    v-if="description"
                    class="line-clamp-2 text-sm leading-relaxed text-[var(--brand-muted)]"
                >
                    {{ description }}
                </p>

                <div
                    class="mt-auto flex items-center justify-between gap-3 border-t border-[var(--brand-primary)]/15 pt-3"
                >
                    <span class="font-brand-heading text-lg text-[var(--brand-primary)]">
                        {{ formatMoney(item.price, currency, locale) }}
                    </span>
                    <span
                        v-if="hasDiscount(item)"
                        class="text-xs text-[var(--brand-muted)] line-through"
                    >
                        {{
                            formatMoney(item.compare_at_price, currency, locale)
                        }}
                    </span>
                </div>
            </div>
        </button>
    </article>
</template>
