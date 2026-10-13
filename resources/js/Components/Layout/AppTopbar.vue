<script setup>
import AppLogo from '@/Components/Shared/AppLogo.vue';
import Icon from '@/Components/UI/Icon.vue';
import RestaurantSelector from './RestaurantSelector.vue';
import ThemeToggle from './ThemeToggle.vue';
import UserMenu from './UserMenu.vue';

defineEmits(['toggle-sidebar']);
</script>

<template>
    <header
        class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80 sm:px-6"
    >
        <button
            type="button"
            class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 lg:hidden"
            aria-label="القائمة"
            @click="$emit('toggle-sidebar')"
        >
            <Icon name="menu" class="h-5 w-5" />
        </button>

        <div class="lg:hidden">
            <AppLogo class="h-7 w-auto text-brand-600 dark:text-brand-400" />
        </div>

        <div class="hidden lg:block">
            <RestaurantSelector />
        </div>

        <div class="flex-1" />

        <div class="flex items-center gap-1">
            <ThemeToggle />
            <UserMenu />
        </div>
    </header>
</template>
