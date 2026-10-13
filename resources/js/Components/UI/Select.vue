<script setup>
defineProps({
    modelValue: {
        type: [String, Number, null],
        default: '',
    },
    error: {
        type: String,
        default: null,
    },
    options: {
        type: Array,
        default: () => [],
    },
});
defineEmits(['update:modelValue']);

const base =
    'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-slate-50 dark:bg-slate-900 dark:text-slate-100';

const normal =
    'border-slate-300 focus:border-brand-500 focus:ring-brand-500/40 dark:border-slate-700';

const invalid =
    'border-rose-400 focus:border-rose-500 focus:ring-rose-500/40 dark:border-rose-500/60';
</script>

<template>
    <select
        :value="modelValue"
        :class="[base, error ? invalid : normal]"
        @change="$emit('update:modelValue', $event.target.value)"
    >
        <option
            v-for="option in options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
        <slot />
    </select>
</template>
