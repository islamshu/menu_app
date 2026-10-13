<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
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
    accent: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['open']);

const name = computed(() => translate(props.category.name, props.locale));
const description = computed(() =>
    translate(props.category.description, props.locale),
);
const isAccent = computed(
    () => props.accent || props.category.virtual === true,
);
</script>

<template>
    <section
        :id="categoryAnchor(category)"
        class="scroll-mt-44"
        :class="
            isAccent
                ? 'rounded-3xl border border-ember-200/80 bg-gradient-to-b from-ember-50 via-ember-50/50 to-paper p-5 shadow-sm sm:p-8'
                : ''
        "
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
                v-if="isAccent"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-ember-500 text-white shadow-[0_12px_26px_-12px_rgba(196,98,44,0.9)]"
                aria-hidden="true"
            >
                <Icon name="flame" class="h-5 w-5" />
            </span>
            <span
                v-else
                class="font-display text-lg leading-none text-ember-500 tabular-nums"
                aria-hidden="true"
            >
                {{ String(index).padStart(2, '0') }}
            </span>

            <div class="min-w-0 flex-1">
                <h2
                    class="font-display text-2xl leading-tight sm:text-3xl"
                    :class="isAccent ? 'text-ember-700' : 'text-ink'"
                >
                    {{ name }}
                </h2>
                <p
                    v-if="description"
                    class="mt-1.5 max-w-2xl text-sm leading-relaxed"
                    :class="isAccent ? 'text-ember-900/70' : 'text-ink-muted'"
                >
                    {{ description }}
                </p>
            </div>

            <span class="hidden items-center gap-3 pb-1 sm:flex">
                <span
                    class="h-px w-16"
                    :class="isAccent ? 'bg-ember-300' : 'bg-ink-line'"
                    aria-hidden="true"
                />
                <span
                    class="text-xs tabular-nums"
                    :class="isAccent ? 'text-ember-600' : 'text-ink-faint'"
                >
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
