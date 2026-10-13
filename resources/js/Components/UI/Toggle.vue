<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const toggle = () => {
    if (props.disabled) return;

    emit('update:modelValue', !props.modelValue);
};
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :aria-label="label ?? undefined"
        :disabled="disabled"
        class="flex h-6 w-11 shrink-0 items-center rounded-full p-0.5 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus-visible:ring-offset-slate-950"
        :class="
            modelValue
                ? 'justify-end bg-brand-600'
                : 'justify-start bg-slate-300 dark:bg-slate-700'
        "
        @click="toggle"
    >
        <span class="h-5 w-5 rounded-full bg-white shadow-sm transition" />
    </button>
</template>
