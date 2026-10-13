<script setup>
import { computed } from 'vue';
import MenuProductCard from './MenuProductCard.vue';
import { categoryAnchor, translate } from '@/Support/menu';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
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

const name = computed(() => translate(props.category.name, props.locale));
const description = computed(() =>
    translate(props.category.description, props.locale),
);
</script>

<template>
    <section
        :id="categoryAnchor(category)"
        class="scroll-mt-44"
        :aria-label="name"
    >
        <img
            v-if="category.image"
            :src="category.image"
            :alt="name"
            loading="lazy"
            decoding="async"
            class="mb-6 h-40 w-full rounded-2xl object-cover shadow-sm sm:h-52"
        />

        <div class="mb-6 flex items-end gap-4">
            <span
                class="font-display text-lg leading-none text-ember-500 tabular-nums"
                aria-hidden="true"
            >
                {{ String(index).padStart(2, '0') }}
            </span>

            <div class="min-w-0 flex-1">
                <h2
                    class="font-display text-2xl leading-tight text-ink sm:text-3xl"
                >
                    {{ name }}
                </h2>
                <p
                    v-if="description"
                    class="mt-1.5 max-w-2xl text-sm leading-relaxed text-ink-muted"
                >
                    {{ description }}
                </p>
            </div>

            <span class="hidden items-center gap-3 pb-1 sm:flex">
                <span class="h-px w-16 bg-ink-line" aria-hidden="true" />
                <span class="text-xs tabular-nums text-ink-faint">
                    {{ category.items.length }}
                </span>
            </span>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <MenuProductCard
                v-for="item in category.items"
                :key="item.id"
                :item="item"
                :locale="locale"
                :currency="currency"
                @open="$emit('open', $event)"
            />
        </div>
    </section>
</template>
