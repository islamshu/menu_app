<script setup>
import Icon from '@/Components/UI/Icon.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: null,
    },
    icon: {
        type: String,
        default: 'image',
    },
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-ink-line bg-white/70 px-6 py-20 text-center"
    >
        <span
            class="flex h-16 w-16 items-center justify-center rounded-full bg-cream text-ember-500"
        >
            <Icon :name="icon" class="h-7 w-7" />
        </span>

        <h3 class="mt-5 font-display text-xl text-ink">
            {{ title }}
        </h3>

        <p
            v-if="description"
            class="mt-2 max-w-sm text-sm leading-relaxed text-ink-muted"
        >
            {{ description }}
        </p>

        <slot />
    </div>
</template>
