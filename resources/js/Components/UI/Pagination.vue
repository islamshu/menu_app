<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap items-center justify-center gap-1"
        role="navigation"
        aria-label="ترقيم الصفحات"
    >
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="rounded-lg px-3 py-2 text-xs text-slate-400 dark:text-slate-600"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                class="rounded-lg px-3 py-2 text-xs font-medium transition"
                :class="
                    link.active
                        ? 'bg-brand-600 text-white'
                        : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                "
                v-html="link.label"
            />
        </template>
    </nav>
</template>
