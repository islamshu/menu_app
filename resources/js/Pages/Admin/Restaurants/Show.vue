<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import Dialog from '@/Components/UI/Dialog.vue';
import Icon from '@/Components/UI/Icon.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    members: {
        type: Array,
        default: () => [],
    },
    subscriptions: {
        type: Array,
        default: () => [],
    },
    plans: {
        type: Array,
        default: () => [],
    },
});

const roleLabels = {
    owner: 'مالك',
    manager: 'مدير',
    staff: 'موظف',
};

const statusVariant = (value) =>
    value === 'active' ? 'success' : value === 'suspended' ? 'warning' : 'neutral';

const subscriptionVariant = (status) =>
    status === 'active' || status === 'trialing'
        ? 'success'
        : status === 'past_due'
          ? 'warning'
          : 'neutral';

const toggleStatus = () => {
    router.patch(
        route('admin.restaurants.status', props.restaurant.id),
        { status: props.restaurant.status === 'active' ? 'suspended' : 'active' },
        { preserveScroll: true },
    );
};

const showAssign = ref(false);
const form = useForm({
    restaurant_id: props.restaurant.id,
    plan_id: '',
    status: 'active',
    starts_at: '',
    ends_at: '',
});

const openAssign = () => {
    form.reset();
    form.clearErrors();
    form.restaurant_id = props.restaurant.id;
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

const deletingSub = ref(null);
const deleteSubForm = useForm({});
const confirmDeleteSub = () => {
    if (!deletingSub.value) return;
    deleteSubForm.delete(route('admin.subscriptions.destroy', deletingSub.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deletingSub.value = null;
        },
    });
};

const deleting = ref(false);
const deleteForm = useForm({});
const confirmDelete = () => {
    deleteForm.delete(route('admin.restaurants.destroy', props.restaurant.id));
};
</script>

<template>
    <Head :title="restaurant.name" />

    <AdminLayout>
        <div class="mb-4">
            <Link
                :href="route('admin.restaurants.index')"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
            >
                <Icon name="arrow-left" class="h-4 w-4" />
                كل المطاعم
            </Link>
        </div>

        <PageHeader :title="restaurant.name" :subtitle="restaurant.slug">
            <template #actions>
                <a
                    :href="restaurant.menu_url"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <Icon name="eye" class="h-4 w-4" />
                    القائمة العامة
                </a>
                <Button
                    :variant="restaurant.status === 'active' ? 'outline' : 'primary'"
                    @click="toggleStatus"
                >
                    <template #icon>
                        <Icon :name="restaurant.status === 'active' ? 'x' : 'check'" class="h-4 w-4" />
                    </template>
                    {{ restaurant.status === 'active' ? 'إيقاف المطعم' : 'تفعيل المطعم' }}
                </Button>
                <Button variant="danger" @click="deleting = true">
                    <template #icon>
                        <Icon name="trash" class="h-4 w-4" />
                    </template>
                    حذف
                </Button>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card title="الحالة">
                <Badge :variant="statusVariant(restaurant.status)" dot>
                    {{ restaurant.status_label }}
                </Badge>
                <p class="mt-3 text-xs text-slate-400">
                    انضم بتاريخ {{ restaurant.created_at }}
                </p>
            </Card>

            <Card title="الاستخدام">
                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex justify-between">
                        <span>الأقسام</span><span class="font-semibold">{{ restaurant.categories_count }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span>الأصناف</span><span class="font-semibold">{{ restaurant.items_count }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span>المستخدمون</span><span class="font-semibold">{{ restaurant.users_count }}</span>
                    </li>
                </ul>
            </Card>

            <Card title="الإعدادات">
                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex justify-between">
                        <span>العملة</span><span class="font-semibold">{{ restaurant.currency }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span>اللغة</span><span class="font-semibold">{{ restaurant.default_locale }}</span>
                    </li>
                    <li class="flex justify-between gap-2">
                        <span>المنطقة الزمنية</span>
                        <span class="truncate font-semibold" :title="restaurant.timezone">{{ restaurant.timezone }}</span>
                    </li>
                </ul>
            </Card>

            <Card title="التواصل">
                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <li class="truncate">{{ restaurant.email || '—' }}</li>
                    <li class="truncate">{{ restaurant.phone || '—' }}</li>
                    <li class="truncate">{{ restaurant.address || '—' }}</li>
                </ul>
            </Card>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <Card title="الأعضاء">
                <ul v-if="members.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li
                        v-for="member in members"
                        :key="member.id"
                        class="flex items-center justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ member.name }}
                            </p>
                            <p class="truncate text-xs text-slate-400">{{ member.email }}</p>
                        </div>
                        <Badge variant="neutral">
                            {{ roleLabels[member.role] ?? member.role }}
                        </Badge>
                    </li>
                </ul>
                <p v-else class="py-4 text-center text-sm text-slate-400">
                    لا يوجد أعضاء مرتبطون بهذا المطعم.
                </p>
            </Card>

            <Card title="الاشتراكات">
                <template #header>
                    <div class="flex w-full items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            الاشتراكات
                        </h3>
                        <Button variant="primary" size="sm" @click="openAssign">
                            <template #icon>
                                <Icon name="plus" class="h-3.5 w-3.5" />
                            </template>
                            إسناد اشتراك
                        </Button>
                    </div>
                </template>

                <ul v-if="subscriptions.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li
                        v-for="subscription in subscriptions"
                        :key="subscription.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ subscription.plan }}
                            </p>
                            <p class="text-xs text-slate-400">
                                <span v-if="subscription.starts_at">{{ subscription.starts_at }}</span>
                                <span v-if="subscription.ends_at"> ← {{ subscription.ends_at }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
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
                            <Button variant="danger-ghost" size="icon" @click="deletingSub = subscription">
                                <Icon name="trash" class="h-4 w-4" />
                            </Button>
                        </div>
                    </li>
                </ul>
                <p v-else class="py-4 text-center text-sm text-slate-400">
                    لا توجد اشتراكات. أسند باقة للبدء.
                </p>
            </Card>
        </div>

        <Dialog
            :show="showAssign"
            title="إسناد اشتراك"
            @close="showAssign = false"
        >
            <form class="space-y-4" @submit.prevent="submitAssign">
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
                        :disabled="!plans.length"
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
            :show="Boolean(deletingSub)"
            title="حذف الاشتراك"
            message="هل أنت متأكد من حذف سجل الاشتراك؟"
            @confirm="confirmDeleteSub"
            @close="deletingSub = null"
        />

        <ConfirmDialog
            :show="deleting"
            title="حذف المطعم"
            :message="'سيتم حذف مطعم «' + restaurant.name + '» وكل بياناته نهائياً. هل أنت متأكد؟'"
            confirm-label="حذف نهائي"
            :processing="deleteForm.processing"
            @confirm="confirmDelete"
            @close="deleting = false"
        />
    </AdminLayout>
</template>
