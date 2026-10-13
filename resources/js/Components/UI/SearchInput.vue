<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'بحث...',
    },
    delay: {
        type: Number,
        default: 300,
    },
});

const emit = defineEmits(['update:modelValue']);

const local = ref(props.modelValue ?? '');
let timer = null;

watch(
    () => props.modelValue,
    (value) => {
        if (value !== local.value) local.value = value ?? '';
    },
);

watch(local, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => emit('update:modelValue', value), props.delay);
});

const clear = () => {
    local.value = '';
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="relative">
        <span
            class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"
                />
            </svg>
        </span>

        <input
            v-model="local"
            type="search"
            :placeholder="placeholder"
            class="block w-full rounded-lg border border-slate-300 bg-white py-2 pe-9 ps-9 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
        />

        <button
            v-if="local"
            type="button"
            class="absolute inset-y-0 end-2 my-auto flex h-6 w-6 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
            aria-label="مسح البحث"
            @click="clear"
        >
            <svg
                class="h-3.5 w-3.5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>
</template>
