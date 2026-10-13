<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
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
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import Toggle from '@/Components/UI/Toggle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    intervals: {
        type: Array,
        default: () => [],
    },
});

const showForm = ref(false);
const editing = ref(null);
const form = useForm({
    name: '',
    price: '',
    billing_interval: 'monthly',
    is_active: true,
    features: [],
    limits: { restaurants: '', menu_items: '' },
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.features = [''];
    showForm.value = true;
};

const openEdit = (plan) => {
    editing.value = plan;
    form.clearErrors();
    form.name = plan.name;
    form.price = plan.price;
    form.billing_interval = plan.billing_interval;
    form.is_active = plan.is_active;
    form.features = plan.features.length ? [...plan.features] : [''];
    form.limits = {
        restaurants: plan.limits.restaurants ?? '',
        menu_items: plan.limits.menu_items ?? '',
    };
    showForm.value = true;
};

const addFeature = () => form.features.push('');

const removeFeature = (index) => form.features.splice(index, 1);

const cleanedFeatures = () =>
    form.features.map((feature) => feature.trim()).filter((feature) => feature !== '');

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            features: cleanedFeatures(),
        }))
        .post(route('admin.plans.store'), {
            onSuccess: () => {
                showForm.value = false;
            },
        });
};

const update = () => {
    form
        .transform((data) => ({
            ...data,
            features: cleanedFeatures(),
        }))
        .put(route('admin.plans.update', editing.value.id), {
            onSuccess: () => {
                showForm.value = false;
                editing.value = null;
            },
        });
};

const save = () => (editing.value ? update() : submit());

const toggle = (plan) => {
    router.patch(route('admin.plans.toggle', plan.id), {}, { preserveScroll: true });
};

const deleting = ref(null);
const deleteForm = useForm({});
const confirmDelete = () => {
    if (!deleting.value) return;
    deleteForm.delete(route('admin.plans.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
    });
};

const formatPrice = (price) => {
    const value = parseFloat(price);
    return Number.isNaN(value) ? price : value.toLocaleString();
};
</script>

<template>
    <Head title="الباقات" />

    <AdminLayout>
        <PageHeader
            title="الباقات"
            subtitle="أنشئ باقات الاشتراك وحدّد ميزاتها وحدودها."
        >
            <template #actions>
                <Button variant="primary" @click="openCreate">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    باقة جديدة
                </Button>
            </template>
        </PageHeader>

        <EmptyState
            v-if="!plans.length"
            title="لا توجد باقات بعد"
            description="ابدأ بإنشاء أول باقة اشتراك."
        >
            <template #action>
                <Button variant="primary" @click="openCreate">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    باقة جديدة
                </Button>
            </template>
        </EmptyState>

        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="plan in plans" :key="plan.id">
                <template #header>
                    <div class="flex w-full items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ plan.name }}
                            </h3>
                            <Badge :variant="plan.is_active ? 'success' : 'neutral'">
                                {{ plan.is_active ? 'نشطة' : 'موقوفة' }}
                            </Badge>
                        </div>
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ formatPrice(plan.price) }}
                            <span class="text-xs font-normal text-slate-400">
                                / {{ plan.billing_interval_label }}
                            </span>
                        </span>
                    </div>
                </template>

                <ul v-if="plan.features.length" class="space-y-1.5">
                    <li
                        v-for="(feature, index) in plan.features"
                        :key="index"
                        class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"
                    >
                        <Icon name="check" class="h-3.5 w-3.5 text-emerald-500" />
                        {{ feature }}
                    </li>
                </ul>
                <p v-else class="text-sm text-slate-400">لا ميزات محددة.</p>

                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-400 dark:border-slate-800">
                    <span>{{ plan.subscriptions_count }} اشتراك</span>
                    <span v-if="plan.limits.menu_items">حتى {{ plan.limits.menu_items }} صنف</span>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <Button variant="secondary" size="sm" @click="openEdit(plan)">
                        <template #icon>
                            <Icon name="edit" class="h-3.5 w-3.5" />
                        </template>
                        تعديل
                    </Button>
                    <Button variant="outline" size="sm" @click="toggle(plan)">
                        {{ plan.is_active ? 'إيقاف' : 'تفعيل' }}
                    </Button>
                    <Button variant="danger" size="sm" class="ms-auto" @click="deleting = plan">
                        <template #icon>
                            <Icon name="trash" class="h-3.5 w-3.5" />
                        </template>
                    </Button>
                </div>
            </Card>
        </div>

        <Dialog
            :show="showForm"
            :title="editing ? 'تعديل الباقة' : 'باقة جديدة'"
            max-width="2xl"
            @close="showForm = false"
        >
            <form class="space-y-5" @submit.prevent="save">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="اسم الباقة" />
                        <TextInput v-model="form.name" type="text" class="mt-2 w-full" placeholder="مثال: الاحترافية" />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="السعر" />
                        <TextInput v-model="form.price" type="number" step="0.01" min="0" dir="ltr" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="form.errors.price" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="دورة الفوترة" />
                        <Select
                            v-model="form.billing_interval"
                            class="mt-2 w-full"
                            :options="intervals"
                        />
                        <InputError class="mt-1" :message="form.errors.billing_interval" />
                    </div>
                    <div>
                        <InputLabel value="حد المطاعم" />
                        <TextInput v-model="form.limits.restaurants" type="number" min="0" dir="ltr" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="form.errors['limits.restaurants']" />
                    </div>
                    <div>
                        <InputLabel value="حد الأصناف" />
                        <TextInput v-model="form.limits.menu_items" type="number" min="0" dir="ltr" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="form.errors['limits.menu_items']" />
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <InputLabel value="الميزات" />
                        <Button variant="ghost" size="sm" @click="addFeature">
                            <template #icon>
                                <Icon name="plus" class="h-3.5 w-3.5" />
                            </template>
                            إضافة ميزة
                        </Button>
                    </div>
                    <div class="mt-2 space-y-2">
                        <div
                            v-for="(feature, index) in form.features"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <TextInput v-model="form.features[index]" type="text" class="w-full" placeholder="مثال: قائمة متعددة اللغات" />
                            <Button variant="danger-ghost" size="icon" @click="removeFeature(index)">
                                <Icon name="x" class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">باقة نشطة</p>
                        <p class="text-xs text-slate-400">تظهر للمطاعم عند إسناد الاشتراكات.</p>
                    </div>
                    <Toggle v-model="form.is_active" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <Button variant="ghost" @click="showForm = false">إلغاء</Button>
                    <Button variant="primary" type="submit" :loading="form.processing">
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
            title="حذف الباقة"
            :message="deleting ? 'سيتم حذف باقة «' + deleting.name + '» نهائياً.' : ''"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </AdminLayout>
</template>
