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
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    subscriptions: {
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
    restaurants: {
        type: Array,
        default: () => [],
    },
    plans: {
        type: Array,
        default: () => [],
    },
});

const status = ref(props.filters.status ?? '');
watch(status, (value) => {
    router.get(
        route('admin.subscriptions.index'),
        { status: value, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const statusVariant = (value) =>
    value === 'active' || value === 'trialing'
        ? 'success'
        : value === 'past_due'
          ? 'warning'
          : 'neutral';

const showAssign = ref(false);
const form = useForm({
    restaurant_id: '',
    plan_id: '',
    status: 'active',
    starts_at: '',
    ends_at: '',
});

const openAssign = () => {
    form.reset();
    form.clearErrors();
    form.restaurant_id = props.restaurants[0]?.id ?? '';
    form.plan_id = props.plans[0]?.id ?? '';
    form.status = 'active';
    showAssign.value = true;
};

const submitAssign = () => {
    form.post(route('admin.subscriptions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAssign.value = false;
            form.reset();
        },
    });
};

const updateStatus = (subscription, value) => {
    router.patch(
        route('admin.subscriptions.status', subscription.id),
        { status: value },
        { preserveScroll: true },
    );
};

const deleting = ref(null);
const deleteForm = useForm({});
const confirmDelete = () => {
    if (!deleting.value) return;
    deleteForm.delete(route('admin.subscriptions.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};
</script>

<template>
    <Head title="الاشتراكات" />

    <AdminLayout>
        <PageHeader
            title="الاشتراكات"
            subtitle="أسند الباقات للمطاعم وتابع حالة كل اشتراك."
        >
            <template #actions>
                <Button variant="primary" @click="openAssign">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    إسناد اشتراك
                </Button>
            </template>
        </PageHeader>

        <Card>
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-4 dark:border-slate-800">
                <div class="w-full max-w-xs">
                    <Select
                        v-model="status"
                        :options="[
                            { value: '', label: 'كل الحالات' },
                            ...statuses.map((s) => ({ value: s.value, label: s.label })),
                        ]"
                    />
                </div>
                <span class="text-sm text-slate-400">
                    {{ subscriptions.total ?? 0 }} اشتراك
                </span>
            </div>

            <EmptyState
                v-if="!subscriptions.data.length"
                title="لا توجد اشتراكات"
                description="أسند باقة لأحد المطاعم للبدء."
            >
                <template #action>
                    <Button variant="primary" @click="openAssign">
                        <template #icon>
                            <Icon name="plus" class="h-4 w-4" />
                        </template>
                        إسناد اشتراك
                    </Button>
                </template>
            </EmptyState>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                المطعم
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                الباقة
                            </th>
                            <th class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 md:table-cell dark:text-slate-400">
                                الفترة
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                الحالة
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="subscription in subscriptions.data"
                            :key="subscription.id"
                            class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    v-if="subscription.restaurant"
                                    :href="route('admin.restaurants.show', subscription.restaurant.id)"
                                    class="text-sm font-medium text-slate-900 hover:text-brand-600 dark:text-slate-100 dark:hover:text-brand-400"
                                >
                                    {{ subscription.restaurant.name }}
                                </Link>
                                <span v-else class="text-xs text-slate-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-slate-600 dark:text-slate-300">
                                    {{ subscription.plan }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-slate-400 md:table-cell">
                                <span>{{ subscription.starts_at || '—' }}</span>
                                <span v-if="subscription.ends_at"> ← {{ subscription.ends_at }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <Select
                                    :model-value="subscription.status"
                                    class="w-32"
                                    :options="[
                                        { value: 'active', label: 'نشط' },
                                        { value: 'trialing', label: 'تجريبي' },
                                        { value: 'past_due', label: 'متأخر' },
                                        { value: 'canceled', label: 'ملغى' },
                                        { value: 'expired', label: 'منتهي' },
                                    ]"
                                    @update:model-value="updateStatus(subscription, $event)"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <Button variant="danger-ghost" size="icon" @click="deleting = subscription">
                                    <Icon name="trash" class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="subscriptions.links && subscriptions.links.length > 3"
                :links="subscriptions.links"
                class="border-t border-slate-100 p-4 dark:border-slate-800"
            />
        </Card>

        <Dialog
            :show="showAssign"
            title="إسناد اشتراك"
            @close="showAssign = false"
        >
            <form class="space-y-4" @submit.prevent="submitAssign">
                <div>
                    <InputLabel value="المطعم" />
                    <Select
                        v-model="form.restaurant_id"
                        class="mt-2 w-full"
                        :options="restaurants.map((r) => ({ value: r.id, label: r.name }))"
                    />
                    <InputError class="mt-1" :message="form.errors.restaurant_id" />
                </div>

                <div>
                    <InputLabel value="الباقة" />
                    <Select
                        v-model="form.plan_id"
                        class="mt-2 w-full"
                        :options="plans.map((p) => ({ value: p.id, label: p.name }))"
                    />
                    <InputError class="mt-1" :message="form.errors.plan_id" />
                    <p v-if="!plans.length" class="mt-1 text-xs text-amber-600">
                        لا توجد باقات نشطة. أنشئ باقة أولاً.
                    </p>
                </div>

                <div>
                    <InputLabel value="الحالة" />
                    <Select
                        v-model="form.status"
                        class="mt-2 w-full"
                        :options="[
                            { value: 'active', label: 'نشط' },
                            { value: 'trialing', label: 'تجريبي' },
                        ]"
                    />
                    <InputError class="mt-1" :message="form.errors.status" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="تاريخ البداية" />
                        <TextInput v-model="form.starts_at" type="date" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="form.errors.starts_at" />
                    </div>
                    <div>
                        <InputLabel value="تاريخ الانتهاء" />
                        <TextInput v-model="form.ends_at" type="date" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="form.errors.ends_at" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <Button variant="ghost" @click="showAssign = false">إلغاء</Button>
                    <Button
                        variant="primary"
                        type="submit"
                        :loading="form.processing"
                        :disabled="!plans.length || !restaurants.length"
                    >
                        <template #icon>
                            <Icon name="check" class="h-4 w-4" />
                        </template>
                        حفظ
                    </Button>
                </div>
            </form>
        </Dialog>

        <ConfirmDialog
            :show="Boolean(deleting)"
            title="حذف الاشتراك"
            message="هل أنت متأكد من حذف سجل الاشتراك؟"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </AdminLayout>
</template>
