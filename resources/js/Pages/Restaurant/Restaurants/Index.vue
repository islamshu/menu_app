<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    restaurants: {
        type: Array,
        default: () => [],
    },
});

function switchTo(id) {
    router.post(route('restaurants.switch', id));
}
</script>

<template>
    <Head title="Restaurants" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Your Restaurants
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <ul
                        v-if="restaurants.length"
                        class="divide-y divide-gray-100"
                    >
                        <li
                            v-for="restaurant in restaurants"
                            :key="restaurant.id"
                            class="flex items-center justify-between px-6 py-4"
                        >
                            <div>
                                <div
                                    class="text-sm font-medium text-gray-900"
                                >
                                    {{ restaurant.name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ restaurant.role }}
                                </div>
                            </div>

                            <button
                                type="button"
                                class="rounded-md bg-gray-900 px-4 py-2 text-xs font-medium text-white transition hover:bg-gray-700"
                                @click="switchTo(restaurant.id)"
                            >
                                Open
                            </button>
                        </li>
                    </ul>

                    <p v-else class="p-6 text-sm text-gray-600">
                        You are not a member of any restaurant yet.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
