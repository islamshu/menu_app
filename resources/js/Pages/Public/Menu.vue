<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="restaurant.name" />

    <div class="min-h-screen bg-gray-50 px-6 py-12">
        <div class="mx-auto max-w-2xl text-center">
            <h1 class="text-3xl font-semibold text-gray-900">
                {{ restaurant.name }}
            </h1>

            <p v-if="restaurant.description" class="mt-3 text-gray-600">
                {{ restaurant.description }}
            </p>

            <p class="mt-8 text-sm text-gray-500">
                Menu content will be available soon.
            </p>
        </div>
    </div>
</template>
