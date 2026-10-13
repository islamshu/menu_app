<script setup>
defineProps({
    modelValue: {
        type: String,
        default: 'ar',
    },
    locales: {
        type: Array,
        default: () => [
            { code: 'ar', label: 'العربية' },
            { code: 'en', label: 'English' },
        ],
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <div
            class="mb-3 inline-flex gap-1 rounded-lg bg-slate-100 p-1 dark:bg-slate-800"
            role="tablist"
        >
            <button
                v-for="locale in locales"
                :key="locale.code"
                type="button"
                role="tab"
                :aria-selected="modelValue === locale.code"
                class="rounded-md px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                :class="
                    modelValue === locale.code
                        ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                "
                @click="$emit('update:modelValue', locale.code)"
            >
                {{ locale.label }}
            </button>
        </div>

        <slot :locale="modelValue" />
    </div>
</template>
