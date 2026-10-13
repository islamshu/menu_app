<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Platform Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Platform Admin
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div
                        v-for="(value, key) in stats"
                        :key="key"
                        class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div
                            class="text-sm font-medium uppercase tracking-wide text-gray-500"
                        >
                            {{ key }}
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">
                            {{ value }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
