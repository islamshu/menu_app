<script setup>
defineProps({
    title: {
        type: String,
        default: null,
    },
    flush: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900 dark:shadow-card-dark"
    >
        <div
            v-if="title || $slots.header"
            class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-800"
        >
            <slot name="header">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                    {{ title }}
                </h3>
            </slot>
        </div>

        <div :class="flush ? '' : 'p-5 sm:p-6'">
            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="border-t border-slate-100 px-5 py-4 dark:border-slate-800"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
