<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div
        class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
    >
        <div>
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100 sm:text-2xl">
                {{ title }}
            </h1>
            <p
                v-if="subtitle"
                class="mt-1 text-sm text-slate-500 dark:text-slate-400"
            >
                {{ subtitle }}
            </p>
        </div>

        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
