<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Card from '@/Components/UI/Card.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import Icon from '@/Components/UI/Icon.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import { translate } from '@/Support/i18n';

const props = defineProps({
    restaurant: {
        type: Object,
        default: null,
    },
    role: {
        type: String,
        default: null,
    },
    stats: {
        type: Object,
        default: null,
    },
    recentItems: {
        type: Array,
        default: () => [],
    },
    subscription: {
        type: Object,
        default: null,
    },
    restaurantStatus: {
        type: Object,
        default: null,
    },
});

const statCards = computed(() => {
    if (!props.stats) return [];

    return [
        {
            label: 'الأقسام',
            value: props.stats.categories,
            tone: 'brand',
            icon: 'folder',
            href: route('categories.index'),
        },
        {
            label: 'الأصناف',
            value: props.stats.items,
            tone: 'slate',
            icon: 'grid',
            href: route('items.index'),
        },
        {
            label: 'متاح',
            value: props.stats.available,
            tone: 'emerald',
            icon: 'check',
        },
        {
            label: 'غير متاح',
            value: props.stats.unavailable,
            tone: 'amber',
            icon: 'x',
        },
        {
            label: 'مميز',
            value: props.stats.featured,
            tone: 'rose',
            icon: 'star',
        },
    ];
});

const statusBadgeVariant = (status) =>
    status === 'active' || status === 'trialing'
        ? 'success'
        : status === 'past_due'
          ? 'warning'
          : 'neutral';

const formatPrice = (price) => {
    const value = parseFloat(price);
    return Number.isNaN(value) ? price : value.toLocaleString();
};
</script>

<template>
    <Head title="نظرة عامة" />

    <RestaurantLayout>
        <PageHeader
            v-if="restaurant"
            :title="restaurant.name"
            :subtitle="'نظرة عامة على قائمة ' + restaurant.name"
        >
            <template #actions>
                <Link
                    :href="route('categories.index')"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <Icon name="folder" class="h-4 w-4" />
                    الأقسام
                </Link>
                <Link
                    :href="route('items.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700"
                >
                    <Icon name="plus" class="h-4 w-4" />
                    الأصناف
                </Link>
            </template>
        </PageHeader>

        <div v-if="restaurant" class="space-y-6">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
                <template v-for="stat in statCards" :key="stat.label">
                    <Link
                        v-if="stat.href"
                        :href="stat.href"
                        class="transition hover:-translate-y-0.5"
                    >
                        <StatCard
                            :label="stat.label"
                            :value="stat.value"
                            :tone="stat.tone"
                        >
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

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2" :title="'أحدث الأصناف (' + recentItems.length + ')'">
                    <ul v-if="recentItems.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li
                            v-for="item in recentItems"
                            :key="item.id"
                            class="flex items-center gap-4 py-3"
                        >
                            <span
                                class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800"
                            >
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    :alt="translate(item.name)"
                                    class="h-full w-full object-cover"
                                />
                                <Icon v-else name="image" class="h-5 w-5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ translate(item.name) }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ item.created_at }}
                                </p>
                            </div>

                            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ formatPrice(item.price) }}
                            </span>

                            <Badge
                                :variant="item.is_available ? 'success' : 'neutral'"
                                dot
                            >
                                {{ item.is_available ? 'متاح' : 'غير متاح' }}
                            </Badge>
                        </li>
                    </ul>

                    <p v-else class="py-8 text-center text-sm text-slate-400">
                        لم تضف أي أصناف بعد. جرّب إضافة أول صنف من صفحة الأصناف.
                    </p>
                </Card>

                <div class="space-y-6">
                    <Card title="الاشتراك">
                        <template v-if="subscription">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500 dark:text-slate-400">الباقة</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ subscription.plan }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-sm text-slate-500 dark:text-slate-400">الحالة</span>
                                <Badge :variant="statusBadgeVariant(subscription.status)">
                                    {{ subscription.status_label }}
                                </Badge>
                            </div>
                            <div
                                v-if="subscription.ends_at"
                                class="mt-3 flex items-center justify-between"
                            >
                                <span class="text-sm text-slate-500 dark:text-slate-400">ينتهي</span>
                                <span class="text-sm text-slate-900 dark:text-slate-100">
                                    {{ subscription.ends_at }}
                                </span>
                            </div>
                        </template>

                        <p v-else class="py-4 text-center text-sm text-slate-400">
                            لا يوجد اشتراك نشط بعد.
                        </p>
                    </Card>

                    <Card title="حالة المطعم">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500 dark:text-slate-400">الحالة</span>
                            <Badge
                                :variant="restaurantStatus && restaurantStatus.value === 'active' ? 'success' : 'neutral'"
                                dot
                            >
                                {{ restaurantStatus?.label }}
                            </Badge>
                        </div>

                        <p class="mt-4 text-xs text-slate-400">
                            يمكنك تعديل بيانات المطعم وحساباته من صفحة الإعدادات.
                        </p>

                        <Link
                            :href="route('settings.edit')"
                            class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                        >
                            <Icon name="cog" class="h-4 w-4" />
                            إعدادات المطعم
                        </Link>
                    </Card>
                </div>
            </div>
        </div>

        <EmptyState
            v-else
            title="لا يوجد مطعم نشط"
            description="أنت لا تنتمي إلى أي مطعم حالياً. يمكنك الحصول على الوصول من قبل مالك المطعم."
        >
            <template #action>
                <Link
                    :href="route('restaurants.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700"
                >
                    إدارة المطاعم
                </Link>
            </template>
        </EmptyState>
    </RestaurantLayout>
</template>