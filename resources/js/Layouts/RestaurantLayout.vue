<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToasts } from '@/Composables/useToasts';
import ToastContainer from '@/Components/UI/ToastContainer.vue';
import AppSidebar from '@/Components/Layout/AppSidebar.vue';
import AppTopbar from '@/Components/Layout/AppTopbar.vue';

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
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <ToastContainer />

        <AppSidebar class="fixed inset-y-0 start-0 z-30 hidden w-64 lg:flex" />

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
                <AppSidebar
                    class="absolute inset-y-0 start-0 w-72 animate-slide-up shadow-pop"
                    @navigate="sidebarOpen = false"
                />
            </div>
        </Transition>

        <div class="lg:ps-64">
            <AppTopbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
