<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Card from '@/Components/UI/Card.vue';
import Icon from '@/Components/UI/Icon.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    subscriptionBreakdown: {
        type: Array,
        default: () => [],
    },
    recentRestaurants: {
        type: Array,
        default: () => [],
    },
    recentUsers: {
        type: Array,
        default: () => [],
    },
});

const statCards = computed(() => [
    { label: 'المطاعم', value: props.stats.restaurants, tone: 'brand', icon: 'store', href: route('admin.restaurants.index') },
    { label: 'مطاعم نشطة', value: props.stats.activeRestaurants, tone: 'emerald', icon: 'check-circle' },
    { label: 'مطاعم موقوفة', value: props.stats.suspendedRestaurants, tone: 'amber', icon: 'x' },
    { label: 'المستخدمون', value: props.stats.users, tone: 'slate', icon: 'users', href: route('admin.users.index') },
    { label: 'مشرفو المنصة', value: props.stats.superAdmins, tone: 'rose', icon: 'star' },
    { label: 'الباقات', value: props.stats.plans, tone: 'brand', icon: 'wallet', href: route('admin.plans.index') },
    { label: 'اشتراكات نشطة', value: props.stats.activeSubscriptions, tone: 'emerald', icon: 'check-circle', href: route('admin.subscriptions.index') },
    { label: 'إجمالي الاشتراكات', value: props.stats.subscriptions, tone: 'slate', icon: 'tag' },
    { label: 'الأقسام', value: props.stats.categories, tone: 'amber', icon: 'folder' },
    { label: 'الأصناف', value: props.stats.items, tone: 'slate', icon: 'grid' },
]);

const statusVariant = (status) =>
    status === 'active' ? 'success' : status === 'suspended' ? 'warning' : 'neutral';
</script>

<template>
    <Head title="مركز الإدارة" />

    <AdminLayout>
        <PageHeader
            title="مركز الإدارة"
            subtitle="نظرة شاملة على المطاعم والباقات والاشتراكات والمستخدمين."
        />

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
            <template v-for="stat in statCards" :key="stat.label">
                <Link
                    v-if="stat.href"
                    :href="stat.href"
                    class="transition hover:-translate-y-0.5"
                >
                    <StatCard :label="stat.label" :value="stat.value" :tone="stat.tone">
                        <template #icon>
                            <Icon :name="stat.icon" class="h-5 w-5" />
                        </template>
                    </StatCard>
                </Link>
                <StatCard
                    v-else
                    :label="stat.label"
                    :value="stat.value"
                    :tone="stat.tone"
                >
                    <template #icon>
                        <Icon :name="stat.icon" class="h-5 w-5" />
                    </template>
                </StatCard>
            </template>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2" title="أحدث المطاعم">
                <ul v-if="recentRestaurants.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li
                        v-for="restaurant in recentRestaurants"
                        :key="restaurant.id"
                        class="flex items-center gap-4 py-3"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800"
                        >
                            <Icon name="store" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <Link
                                :href="route('admin.restaurants.show', restaurant.id)"
                                class="truncate text-sm font-medium text-slate-900 hover:text-brand-600 dark:text-slate-100 dark:hover:text-brand-400"
                            >
                                {{ restaurant.name }}
                            </Link>
                            <p class="truncate text-xs text-slate-400">
                                {{ restaurant.categories_count }} قسم · {{ restaurant.items_count }} صنف
                            </p>
                        </div>
                        <Badge :variant="statusVariant(restaurant.status)" dot>
                            {{ restaurant.status_label }}
                        </Badge>
                    </li>
                </ul>
                <p v-else class="py-8 text-center text-sm text-slate-400">
                    لا توجد مطاعم بعد.
                </p>
            </Card>

            <div class="space-y-6">
                <Card title="توزيع الاشتراكات">
                    <ul class="space-y-3">
                        <li
                            v-for="row in subscriptionBreakdown"
                            :key="row.status"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-slate-600 dark:text-slate-300">
                                {{ row.label }}
                            </span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ row.count }}
                            </span>
                        </li>
                    </ul>
                </Card>

                <Card title="أحدث المستخدمين">
                    <ul v-if="recentUsers.length" class="space-y-3">
                        <li
                            v-for="user in recentUsers"
                            :key="user.id"
                            class="flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-xs text-slate-400">
                                    {{ user.email }}
                                </p>
                            </div>
                            <Badge v-if="user.is_super_admin" variant="brand">
                                مشرف
                            </Badge>
                        </li>
                    </ul>
                    <p v-else class="py-4 text-center text-sm text-slate-400">
                        لا يوجد مستخدمون بعد.
                    </p>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
