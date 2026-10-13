<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'neutral',
    },
    dot: {
        type: Boolean,
        default: false,
    },
});

const variants = {
    neutral:
        'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    success:
        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    warning:
        'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    danger: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
    brand: 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
};

const classes = computed(() => variants[props.variant] ?? variants.neutral);
</script>

<template>
    <span
        :class="classes"
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
    >
        <span
            v-if="dot"
            class="h-1.5 w-1.5 rounded-full bg-current"
        />
        <slot />
    </span>
</template>
