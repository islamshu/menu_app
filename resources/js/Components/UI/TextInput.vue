<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    error: {
        type: String,
        default: null,
    },
});
defineEmits(['update:modelValue']);

const base =
    'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-slate-50 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500';

const normal =
    'border-slate-300 focus:border-brand-500 focus:ring-brand-500/40 dark:border-slate-700';

const invalid =
    'border-rose-400 focus:border-rose-500 focus:ring-rose-500/40 dark:border-rose-500/60';
</script>

<template>
    <input
        :type="type"
        :value="modelValue"
        :class="[base, error ? invalid : normal]"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
