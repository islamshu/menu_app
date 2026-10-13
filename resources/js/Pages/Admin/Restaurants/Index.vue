<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import Icon from '@/Components/UI/Icon.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import Select from '@/Components/UI/Select.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    restaurants: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

watch(search, (value) => {
    router.get(
        route('admin.restaurants.index'),
        { search: value, status: status.value, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

watch(status, (value) => {
    router.get(
        route('admin.restaurants.index'),
        { search: search.value, status: value, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const statusVariant = (value) => (value === 'active' ? 'success' : 'warning');

const toggleStatus = (restaurant) => {
    router.patch(
        route('admin.restaurants.status', restaurant.id),
        { status: restaurant.status === 'active' ? 'suspended' : 'active' },
        { preserveScroll: true },
    );
};

const deleting = ref(null);
const deleteForm = useForm({});
const confirmDelete = () => {
    if (!deleting.value) return;
    deleteForm.delete(route('admin.restaurants.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};
</script>

<template>
    <Head title="المطاعم" />

    <AdminLayout>
        <PageHeader
            title="المطاعم"
            subtitle="أدر كل المطاعم على المنصة، حالتها واستخدامها."
        />

        <Card>
            <div class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:flex-row sm:items-center dark:border-slate-800">
                <div class="w-full sm:max-w-sm">
                    <SearchInput v-model="search" placeholder="ابحث بالاسم أو الرابط أو البريد..." />
                </div>
                <div class="w-full sm:w-48">
                    <Select
                        v-model="status"
                        :options="[
                            { value: '', label: 'كل الحالات' },
                            ...statuses.map((s) => ({ value: s.value, label: s.label })),
                        ]"
                    />
                </div>
                <span class="text-sm text-slate-400 sm:ms-auto">
                    {{ restaurants.total ?? 0 }} مطعم
                </span>
            </div>

            <EmptyState
                v-if="!restaurants.data.length"
                title="لا توجد مطاعم"
                description="لم يتم العثور على مطاعم مطابقة."
            />

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                المطعم
                            </th>
                            <th class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 md:table-cell dark:text-slate-400">
                                الباقة
                            </th>
                            <th class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 lg:table-cell dark:text-slate-400">
                                المحتوى
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                الحالة
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                إجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="restaurant in restaurants.data"
                            :key="restaurant.id"
                            class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800"
                                    >
                                        <img
                                            v-if="restaurant.logo"
                                            :src="restaurant.logo"
                                            :alt="restaurant.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <Icon v-else name="store" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <Link
                                            :href="route('admin.restaurants.show', restaurant.id)"
                                            class="truncate text-sm font-medium text-slate-900 hover:text-brand-600 dark:text-slate-100 dark:hover:text-brand-400"
                                        >
                                            {{ restaurant.name }}
                                        </Link>
                                        <p class="truncate text-xs text-slate-400">
                                            {{ restaurant.slug }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <Badge v-if="restaurant.plan" variant="brand">
                                    {{ restaurant.plan }}
                                </Badge>
                                <span v-else class="text-xs text-slate-400">بدون باقة</span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-slate-500 lg:table-cell dark:text-slate-400">
                                {{ restaurant.categories_count }} قسم · {{ restaurant.items_count }} صنف
                                · {{ restaurant.users_count }} مستخدم
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(restaurant.status)" dot>
                                    {{ restaurant.status_label }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <Button
                                        :variant="restaurant.status === 'active' ? 'outline' : 'secondary'"
                                        size="sm"
                                        @click="toggleStatus(restaurant)"
                                    >
                                        <template #icon>
                                            <Icon :name="restaurant.status === 'active' ? 'x' : 'check'" class="h-3.5 w-3.5" />
                                        </template>
                                        {{ restaurant.status === 'active' ? 'إيقاف' : 'تفعيل' }}
                                    </Button>
                                    <Button variant="danger" size="sm" @click="deleting = restaurant">
                                        <template #icon>
                                            <Icon name="trash" class="h-3.5 w-3.5" />
                                        </template>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="restaurants.links && restaurants.links.length > 3"
                :links="restaurants.links"
                class="border-t border-slate-100 p-4 dark:border-slate-800"
            />
        </Card>

        <ConfirmDialog
            :show="Boolean(deleting)"
            title="حذف المطعم"
            :message="deleting ? 'سيتم حذف مطعم «' + deleting.name + '» وكل أقسامه وأصنافه واشتراكاته نهائياً. هل أنت متأكد؟' : ''"
            confirm-label="حذف نهائي"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </AdminLayout>
</template>
