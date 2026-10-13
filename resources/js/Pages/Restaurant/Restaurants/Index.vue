<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Icon from '@/Components/UI/Icon.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    restaurants: {
        type: Array,
        default: () => [],
    },
});

const roleLabels = {
    owner: 'مالك',
    manager: 'مدير',
    staff: 'موظف',
};

const roleVariants = {
    owner: 'brand',
    manager: 'warning',
    staff: 'neutral',
};

function switchTo(id) {
    router.post(route('restaurants.switch', id));
}
</script>

<template>
    <Head title="المطاعم" />

    <RestaurantLayout>
        <PageHeader
            title="المطاعم"
            subtitle="اختر المطعم الذي تريد إدارته."
        />

        <Card>
            <ul v-if="restaurants.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                <li
                    v-for="restaurant in restaurants"
                    :key="restaurant.id"
                    class="flex items-center justify-between gap-4 px-5 py-4"
                >
                    <div class="flex min-w-0 items-center gap-4">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400"
                        >
                            <img
                                v-if="restaurant.logo"
                                :src="restaurant.logo"
                                :alt="restaurant.name"
                                class="h-full w-full object-cover"
                            />
                            <Icon v-else name="store" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ restaurant.name }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ restaurant.slug }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <Badge :variant="roleVariants[restaurant.role] ?? 'neutral'">
                            {{ roleLabels[restaurant.role] ?? restaurant.role }}
                        </Badge>
                        <Button variant="primary" size="sm" @click="switchTo(restaurant.id)">
                            <template #icon>
                                <Icon name="arrow-left" class="h-3.5 w-3.5" />
                            </template>
                            فتح
                        </Button>
                    </div>
                </li>
            </ul>

            <p v-else class="p-10 text-center text-sm text-slate-400">
                أنت لا تملك الوصول إلى أي مطعم حالياً.
            </p>
        </Card>
    </RestaurantLayout>
</template>