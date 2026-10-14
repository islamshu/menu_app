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
            return 'border border-transparent bg-[var(--brand-surface)] shadow-[0_20px_45px_-38px_rgba(44,38,34,0.9)]';
        case 'flat':
            return 'border border-transparent bg-[var(--brand-surface)]';
        case 'solid':
            return 'border border-transparent bg-[var(--brand-primary)] text-[var(--brand-on-primary)]';
        default:
            return 'border border-[var(--brand-border)] bg-transparent';
    }
});
</script>

<template>
    <article>
        <button
            type="button"
            class="group flex w-full items-stretch gap-4 rounded-[var(--brand-radius)] p-3 text-start transition duration-300 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--brand-bg)]"
            :class="surface"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <div
                class="relative h-24 w-24 shrink-0 overflow-hidden rounded-[var(--brand-radius)] bg-[var(--brand-border)]/40 sm:h-28 sm:w-28"
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
                    class="flex h-full w-full items-center justify-center"
                    aria-hidden="true"
                >
                    <span
                        class="font-brand-heading text-4xl text-[var(--brand-primary)]/70"
                    >
                        {{ initial }}
                    </span>
                </div>
            </div>

            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-brand-heading text-lg leading-snug">
                        {{ name }}
                    </h3>
                    <div class="text-end">
                        <span
                            class="whitespace-nowrap font-semibold text-[var(--brand-primary)]"
                        >
                            {{ formatMoney(item.price, currency, locale) }}
                        </span>
                        <span
                            v-if="hasDiscount(item)"
                            class="block text-xs text-[var(--brand-muted)] line-through"
                        >
                            {{
                                formatMoney(
                                    item.compare_at_price,
                                    currency,
                                    locale,
                                )
                            }}
                        </span>
                    </div>
                </div>

                <p
                    v-if="description"
                    class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-[var(--brand-muted)]"
                >
                    {{ description }}
                </p>

                <div
                    v-if="item.is_featured || item.is_popular || item.is_new"
                    class="mt-auto flex flex-wrap gap-1.5 pt-3"
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
        </button>
    </article>
</template>
