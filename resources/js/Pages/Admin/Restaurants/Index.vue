<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import Dialog from '@/Components/UI/Dialog.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import Icon from '@/Components/UI/Icon.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import Textarea from '@/Components/UI/Textarea.vue';
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
    plans: {
        type: Array,
        default: () => [],
    },
    ownerOptions: {
        type: Array,
        default: () => [],
    },
    currencies: {
        type: Array,
        default: () => [],
    },
    locales: {
        type: Array,
        default: () => [],
    },
    timezones: {
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

const showCreate = ref(false);
const createForm = useForm({
    name: '',
    slug: '',
    description: '',
    email: '',
    phone: '',
    address: '',
    currency: 'ILS',
    default_locale: 'ar',
    timezone: 'UTC',
    status: 'active',
    owner_id: '',
    plan_id: '',
});

const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
};

const submitCreate = () => {
    createForm.post(route('admin.restaurants.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
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
        >
            <template #actions>
                <Button variant="primary" @click="openCreate">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    مطعم جديد
                </Button>
            </template>
        </PageHeader>

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

        <Dialog
            :show="showCreate"
            title="مطعم جديد"
            max-width="2xl"
            @close="showCreate = false"
        >
            <form class="space-y-5" @submit.prevent="submitCreate">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="اسم المطعم" />
                        <TextInput v-model="createForm.name" type="text" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="createForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="الرابط (اختياري)" />
                        <TextInput v-model="createForm.slug" type="text" dir="ltr" class="mt-2 w-full" placeholder="my-restaurant" />
                        <InputError class="mt-1" :message="createForm.errors.slug" />
                    </div>
                </div>

                <div>
                    <InputLabel value="نبذة" />
                    <Textarea v-model="createForm.description" class="mt-2 w-full" />
                    <InputError class="mt-1" :message="createForm.errors.description" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="رقم الهاتف" />
                        <TextInput v-model="createForm.phone" type="tel" dir="ltr" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="createForm.errors.phone" />
                    </div>
                    <div>
                        <InputLabel value="البريد الإلكتروني" />
                        <TextInput v-model="createForm.email" type="email" dir="ltr" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="createForm.errors.email" />
                    </div>
                    <div>
                        <InputLabel value="العنوان" />
                        <TextInput v-model="createForm.address" type="text" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="createForm.errors.address" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="العملة" />
                        <Select
                            v-model="createForm.currency"
                            class="mt-2 w-full"
                            :options="currencies.map((c) => ({ value: c.code, label: c.label }))"
                        />
                        <InputError class="mt-1" :message="createForm.errors.currency" />
                    </div>
                    <div>
                        <InputLabel value="اللغة الافتراضية" />
                        <Select
                            v-model="createForm.default_locale"
                            class="mt-2 w-full"
                            :options="locales.map((l) => ({ value: l.code, label: l.label }))"
                        />
                        <InputError class="mt-1" :message="createForm.errors.default_locale" />
                    </div>
                    <div>
                        <InputLabel value="المنطقة الزمنية" />
                        <Select
                            v-model="createForm.timezone"
                            class="mt-2 w-full"
                            :options="timezones.map((t) => ({ value: t, label: t }))"
                        />
                        <InputError class="mt-1" :message="createForm.errors.timezone" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="الحالة" />
                        <Select
                            v-model="createForm.status"
                            class="mt-2 w-full"
                            :options="statuses.map((s) => ({ value: s.value, label: s.label }))"
                        />
                        <InputError class="mt-1" :message="createForm.errors.status" />
                    </div>
                    <div>
                        <InputLabel value="المالك (اختياري)" />
                        <Select
                            v-model="createForm.owner_id"
                            class="mt-2 w-full"
                            :options="[{ value: '', label: 'بدون' }, ...ownerOptions.map((u) => ({ value: u.id, label: u.name }))]"
                        />
                        <InputError class="mt-1" :message="createForm.errors.owner_id" />
                    </div>
                    <div>
                        <InputLabel value="الباقة (اختياري)" />
                        <Select
                            v-model="createForm.plan_id"
                            class="mt-2 w-full"
                            :options="[{ value: '', label: 'بدون' }, ...plans.map((p) => ({ value: p.id, label: p.name }))]"
                        />
                        <InputError class="mt-1" :message="createForm.errors.plan_id" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <Button variant="ghost" @click="showCreate = false">إلغاء</Button>
                    <Button variant="primary" type="submit" :loading="createForm.processing">
                        <template #icon>
                            <Icon name="check" class="h-4 w-4" />
                        </template>
                        إنشاء
                    </Button>
                </div>
            </form>
        </Dialog>

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
