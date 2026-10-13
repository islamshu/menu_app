<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/Shared/AppLogo.vue';
import Badge from '@/Components/UI/Badge.vue';
import Icon from '@/Components/UI/Icon.vue';

defineEmits(['navigate']);

const page = usePage();

const items = [
    { route: 'admin.dashboard', label: 'نظرة عامة', icon: 'home', pattern: 'admin.dashboard' },
    { route: 'admin.restaurants.index', label: 'المطاعم', icon: 'store', pattern: 'admin.restaurants.*' },
    { route: 'admin.plans.index', label: 'الباقات', icon: 'wallet', pattern: 'admin.plans.*' },
    { route: 'admin.subscriptions.index', label: 'الاشتراكات', icon: 'tag', pattern: 'admin.subscriptions.*' },
    { route: 'admin.users.index', label: 'المستخدمون', icon: 'users', pattern: 'admin.users.*' },
];

const isActive = (pattern) => route().current(pattern);
</script>

<template>
    <aside
        class="flex flex-col border-e border-slate-200 bg-slate-900 dark:border-slate-800"
    >
        <div
            class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5"
        >
            <AppLogo class="h-8 w-auto text-white" />
            <div class="leading-tight">
                <span class="block text-sm font-semibold text-white">
                    Digital Menu
                </span>
                <span class="block text-xs text-slate-400">مركز الإدارة</span>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3 scrollbar-thin">
            <Link
                v-for="item in items"
                :key="item.route"
                :href="route(item.route)"
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                :class="
                    isActive(item.pattern)
                        ? 'bg-brand-600 text-white'
                        : 'text-slate-300 hover:bg-white/10 hover:text-white'
                "
                @click="$emit('navigate')"
            >
                <Icon :name="item.icon" class="h-5 w-5 shrink-0" />
                {{ item.label }}
            </Link>
        </nav>

        <div class="border-t border-white/10 p-3">
            <div class="rounded-lg bg-white/5 p-3">
                <p class="truncate text-sm font-medium text-white">
                    {{ page.props.auth?.user?.name }}
                </p>
                <Badge variant="brand" class="mt-2">super admin</Badge>
            </div>
        </div>
    </aside>
</template>
