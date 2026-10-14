<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/Shared/AppLogo.vue';
import Badge from '@/Components/UI/Badge.vue';
import Icon from '@/Components/UI/Icon.vue';

defineEmits(['navigate']);

const page = usePage();

const items = [
    { route: 'dashboard', label: 'نظرة عامة', icon: 'home', pattern: 'dashboard' },
    { route: 'categories.index', label: 'الأقسام', icon: 'folder', pattern: 'categories.*' },
    { route: 'items.index', label: 'الأصناف', icon: 'grid', pattern: 'items.*' },
    { route: 'branding.edit', label: 'الهوية البصرية', icon: 'sparkles', pattern: 'branding.*' },
    { route: 'settings.edit', label: 'الإعدادات', icon: 'cog', pattern: 'settings.*' },
];

const isActive = (pattern) => route().current(pattern);
</script>

<template>
    <aside
        class="flex flex-col border-e border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
    >
        <div
            class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-5 dark:border-slate-800"
        >
            <AppLogo class="h-8 w-auto text-brand-600 dark:text-brand-400" />
            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                Digital Menu
            </span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3 scrollbar-thin">
            <Link
                v-for="item in items"
                :key="item.route"
                :href="route(item.route)"
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                :class="
                    isActive(item.pattern)
                        ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white'
                "
                @click="$emit('navigate')"
            >
                <Icon :name="item.icon" class="h-5 w-5 shrink-0" />
                {{ item.label }}
            </Link>
        </nav>

        <div class="border-t border-slate-100 p-3 dark:border-slate-800">
            <div
                v-if="page.props.activeRestaurant"
                class="rounded-lg bg-slate-50 p-3 dark:bg-slate-800/60"
            >
                <div class="flex items-center gap-2">
                    <Icon name="building" class="h-4 w-4 text-slate-400" />
                    <span
                        class="truncate text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        {{ page.props.activeRestaurant.name }}
                    </span>
                </div>
                <Badge variant="brand" class="mt-2">
                    {{ page.props.activeRestaurant.role }}
                </Badge>
            </div>
        </div>
    </aside>
</template>
