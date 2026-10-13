<script setup>
import { computed } from 'vue';
import MenuBadge from './MenuBadge.vue';
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
            class="group flex w-full flex-col overflow-hidden rounded-2xl border border-ink-line bg-white text-start shadow-[0_1px_2px_rgba(28,25,23,0.04)] transition duration-300 hover:-translate-y-1 hover:border-ember-200 hover:shadow-lift focus-visible:ring-2 focus-visible:ring-ember-500 focus-visible:ring-offset-2 focus-visible:ring-offset-paper"
            :aria-label="name"
            @click="$emit('open', item)"
        >
            <div class="relative aspect-[4/3] overflow-hidden bg-cream">
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
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-ember-100 to-cream"
                    aria-hidden="true"
                >
                    <span class="font-display text-5xl text-ember-300/80">
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
                <h3
                    class="font-display text-lg leading-snug text-ink"
                >
                    {{ name }}
                </h3>

                <p
                    v-if="description"
                    class="line-clamp-2 text-sm leading-relaxed text-ink-muted"
                >
                    {{ description }}
                </p>

                <div
                    class="mt-auto flex items-center justify-between gap-3 pt-2"
                >
                    <div class="flex items-baseline gap-2">
                        <span class="text-base font-semibold text-ember-600">
                            {{ formatMoney(item.price, currency, locale) }}
                        </span>
                        <span
                            v-if="hasDiscount(item)"
                            class="text-xs text-ink-faint line-through"
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

                    <span
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-ink-faint"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                            aria-hidden="true"
                        />
                        {{ locale === 'ar' ? 'متاح' : 'Available' }}
                    </span>
                </div>
            </div>
        </button>
    </article>
</template>
