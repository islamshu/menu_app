<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
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
import Toggle from '@/Components/UI/Toggle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    currentUserId: {
        type: Number,
        default: null,
    },
});

const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? '');

watch(search, (value) => {
    router.get(
        route('admin.users.index'),
        { search: value, role: role.value, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

watch(role, (value) => {
    router.get(
        route('admin.users.index'),
        { search: search.value, role: value, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const toggleSuperAdmin = (user) => {
    router.patch(
        route('admin.users.super-admin', user.id),
        {},
        { preserveScroll: true },
    );
};

const showCreate = ref(false);
const editing = ref(null);
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_super_admin: false,
});

const openCreate = () => {
    editing.value = null;
    createForm.reset();
    createForm.clearErrors();
    showCreate.value = true;
};

const openEdit = (user) => {
    editing.value = user;
    createForm.clearErrors();
    createForm.name = user.name;
    createForm.email = user.email;
    createForm.password = '';
    createForm.password_confirmation = '';
    createForm.is_super_admin = user.is_super_admin;
    showCreate.value = true;
};

const save = () => {
    if (editing.value) {
        createForm.put(route('admin.users.update', editing.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showCreate.value = false;
                editing.value = null;
                createForm.reset();
            },
        });

        return;
    }

    createForm.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            createForm.reset();
        },
    });
};
</script>

<template>
    <Head title="المستخدمون" />

    <AdminLayout>
        <PageHeader
            title="المستخدمون"
            subtitle="أدر حسابات المنصة وصلاحيات الإشراف."
        >
            <template #actions>
                <Button variant="primary" @click="openCreate">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    مستخدم جديد
                </Button>
            </template>
        </PageHeader>

        <Card>
            <div class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:flex-row sm:items-center dark:border-slate-800">
                <div class="w-full sm:max-w-sm">
                    <SearchInput v-model="search" placeholder="ابحث بالاسم أو البريد الإلكتروني..." />
                </div>
                <div class="w-full sm:w-48">
                    <Select
                        v-model="role"
                        :options="[
                            { value: '', label: 'كل المستخدمين' },
                            { value: 'super_admin', label: 'مشرفو المنصة' },
                            { value: 'member', label: 'المستخدمون العاديون' },
                        ]"
                    />
                </div>
                <span class="text-sm text-slate-400 sm:ms-auto">
                    {{ users.total ?? 0 }} مستخدم
                </span>
            </div>

            <EmptyState
                v-if="!users.data.length"
                title="لا يوجد مستخدمون"
                description="لم يتم العثور على مستخدمين مطابقين."
            />

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                المستخدم
                            </th>
                            <th class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 md:table-cell dark:text-slate-400">
                                المطاعم
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                الصلاحية
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400">
                                إجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ user.name.charAt(0) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ user.name }}
                                        </p>
                                        <p class="truncate text-xs text-slate-400">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-slate-600 md:table-cell dark:text-slate-300">
                                {{ user.restaurants_count }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="user.is_super_admin ? 'brand' : 'neutral'">
                                    {{ user.is_super_admin ? 'مشرف المنصة' : 'مستخدم' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="openEdit(user)"
                                    >
                                        <template #icon>
                                            <Icon name="edit" class="h-3.5 w-3.5" />
                                        </template>
                                        تعديل
                                    </Button>
                                    <Button
                                        :variant="user.is_super_admin ? 'outline' : 'secondary'"
                                        size="sm"
                                        :disabled="user.id === currentUserId"
                                        @click="toggleSuperAdmin(user)"
                                    >
                                        <template #icon>
                                            <Icon name="star" class="h-3.5 w-3.5" />
                                        </template>
                                        {{ user.is_super_admin ? 'إزالة الإشراف' : 'ترقية لمشرف' }}
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="users.links && users.links.length > 3"
                :links="users.links"
                class="border-t border-slate-100 p-4 dark:border-slate-800"
            />
        </Card>

        <Dialog
            :show="showCreate"
            :title="editing ? 'تعديل المستخدم' : 'مستخدم جديد'"
            @close="showCreate = false"
        >
            <form class="space-y-5" @submit.prevent="save">
                <div>
                    <InputLabel value="الاسم" />
                    <TextInput v-model="createForm.name" type="text" class="mt-2 w-full" />
                    <InputError class="mt-1" :message="createForm.errors.name" />
                </div>

                <div>
                    <InputLabel value="البريد الإلكتروني" />
                    <TextInput v-model="createForm.email" type="email" dir="ltr" class="mt-2 w-full" />
                    <InputError class="mt-1" :message="createForm.errors.email" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel :value="editing ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور'" />
                        <TextInput v-model="createForm.password" type="password" dir="ltr" class="mt-2 w-full" />
                        <InputError class="mt-1" :message="createForm.errors.password" />
                        <p v-if="editing" class="mt-1 text-xs text-slate-400">اتركها فارغة للإبقاء على كلمة المرور الحالية.</p>
                    </div>
                    <div>
                        <InputLabel :value="editing ? 'تأكيد كلمة المرور الجديدة' : 'تأكيد كلمة المرور'" />
                        <TextInput v-model="createForm.password_confirmation" type="password" dir="ltr" class="mt-2 w-full" />
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">مشرف المنصة</p>
                        <p v-if="editing && editing.id === currentUserId" class="text-xs text-amber-600">
                            لا يمكنك إزالة صلاحياتك الخاصة.
                        </p>
                        <p v-else class="text-xs text-slate-400">يمنح صلاحية الوصول الكامل للوحة الإدارة.</p>
                    </div>
                    <Toggle
                        v-model="createForm.is_super_admin"
                        :disabled="editing && editing.id === currentUserId"
                    />
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <Button variant="ghost" @click="showCreate = false">إلغاء</Button>
                    <Button variant="primary" type="submit" :loading="createForm.processing">
                        <template #icon>
                            <Icon name="check" class="h-4 w-4" />
                        </template>
                        {{ editing ? 'حفظ' : 'إنشاء' }}
                    </Button>
                </div>
            </form>
        </Dialog>
    </AdminLayout>
</template>
