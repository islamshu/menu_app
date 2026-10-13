<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    restaurant: {
        type: Object,
        default: null,
    },
    role: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ restaurant ? restaurant.name : 'Workspace' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                >
                    <template v-if="restaurant">
                        <p class="text-sm text-gray-600">
                            You are viewing
                            <span class="font-medium text-gray-900">
                                {{ restaurant.name }}
                            </span>
                            <span v-if="role"> as {{ role }}</span>.
                        </p>

                        <Link
                            :href="route('restaurants.index')"
                            class="mt-4 inline-block text-sm font-medium text-gray-900 underline"
                        >
                            Switch restaurant
                        </Link>
                    </template>

                    <template v-else>
                        <p class="text-sm text-gray-600">
                            You are not a member of any restaurant yet.
                            Restaurant creation will be available in a later
                            phase.
                        </p>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
