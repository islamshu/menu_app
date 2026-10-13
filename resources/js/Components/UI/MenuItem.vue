<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        default: null,
    },
    method: {
        type: String,
        default: 'get',
    },
    as: {
        type: String,
        default: 'a',
    },
    danger: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        class="flex w-full items-center gap-2.5 px-3 py-2 text-start text-sm transition"
        :class="
            danger
                ? 'text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10'
                : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800'
        "
    >
        <slot />
    </Link>
</template>
