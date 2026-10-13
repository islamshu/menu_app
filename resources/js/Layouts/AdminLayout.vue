<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToasts } from '@/Composables/useToasts';
import ToastContainer from '@/Components/UI/ToastContainer.vue';
import AdminSidebar from '@/Components/Layout/AdminSidebar.vue';
import ThemeToggle from '@/Components/Layout/ThemeToggle.vue';
import UserMenu from '@/Components/Layout/UserMenu.vue';

const page = usePage();
const { success, error } = useToasts();

const sidebarOpen = ref(false);

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) success(flash.success);
        if (flash?.error) error(flash.error);
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <div class="min-h-screen bg-slate-100 dark:bg-slate-950">
        <ToastContainer />

        <AdminSidebar class="fixed inset-y-0 start-0 z-30 hidden w-64 lg:flex" />

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                    @click="sidebarOpen = false"
                />
                <AdminSidebar
                    class="absolute inset-y-0 start-0 w-72 animate-slide-up shadow-pop"
                    @navigate="sidebarOpen = false"
                />
            </div>
        </Transition>

        <div class="lg:ps-64">
            <header
                class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80 sm:px-6"
            >
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 lg:hidden"
                    aria-label="القائمة"
                    @click="sidebarOpen = true"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    >
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                <div class="flex-1" />

                <ThemeToggle />
                <UserMenu />
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
