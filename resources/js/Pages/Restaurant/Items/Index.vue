<script setup>
import { nextTick, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import Dialog from '@/Components/UI/Dialog.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import Icon from '@/Components/UI/Icon.vue';
import ImageUploader from '@/Components/UI/ImageUploader.vue';
import LocaleTabs from '@/Components/UI/LocaleTabs.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Toggle from '@/Components/UI/Toggle.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import { translate } from '@/Support/i18n';
import { submitMultipart } from '@/Support/forms';

const props = defineProps({
    items: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const search = ref(props.filters.search ?? '');
const selectedCategory = ref(props.filters.category ?? '');
const availability = ref(props.filters.availability ?? '');

let resetting = false;

const applyFilters = () => {
    if (resetting) return;

    router.get(route('items.index'), {
        search: search.value,
        category: selectedCategory.value,
        availability: availability.value,
        page: 1,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(search, applyFilters);
watch(selectedCategory, applyFilters);
watch(availability, applyFilters);

const resetFilters = async () => {
    resetting = true;
    search.value = '';
    selectedCategory.value = '';
    availability.value = '';
    router.get(route('items.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
    await nextTick();
    resetting = false;
};

const hasFilters = () =>
    Boolean(search.value || selectedCategory.value || availability.value);

const rows = ref([]);
watch(
    () => props.items.data,
    (data) => {
        rows.value = [...data];
    },
    { immediate: true },
);

const showForm = ref(false);
const editing = ref(null);
const formLocale = ref('ar');
const form = useForm({
    name: { ar: '', en: '' },
    description: { ar: '', en: '' },
    menu_category_id: '',
    price: '',
    compare_at_price: '',
    image: null,
    remove_image: false,
    is_available: true,
    is_featured: false,
    is_popular: false,
    is_new: false,
    sort_order: null,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.menu_category_id = props.categories[0]?.id ?? '';
    formLocale.value = 'ar';
    showForm.value = true;
};

const openEdit = (item) => {
    editing.value = item;
    form.name = {
        ar: item.name?.ar ?? '',
        en: item.name?.en ?? '',
    };
    form.description = {
        ar: item.description?.ar ?? '',
        en: item.description?.en ?? '',
    };
    form.menu_category_id = item.menu_category_id ?? '';
    form.price = item.price;
    form.compare_at_price = item.compare_at_price ?? '';
    form.image = null;
    form.remove_image = false;
    form.is_available = item.is_available;
    form.is_featured = item.is_featured;
    form.is_popular = item.is_popular;
    form.is_new = item.is_new;
    form.sort_order = item.sort_order;
    form.clearErrors();
    formLocale.value = 'ar';
    showForm.value = true;
};

const onImage = (file) => {
    form.image = file;
    if (file) {
        form.remove_image = false;
    } else if (editing.value?.image) {
        form.remove_image = true;
    }
};

const submit = () => {
    const options = {
        onSuccess: () => {
            showForm.value = false;
            editing.value = null;
        },
    };

    if (editing.value) {
        submitMultipart(
            form,
            'put',
            route('items.update', editing.value.id),
            options,
        );
    } else {
        submitMultipart(form, 'post', route('items.store'), options);
    }
};

const toggleField = (item, field, value) => {
    router.patch(
        route('items.toggle', item.id),
        { field, value },
        { preserveScroll: true },
    );
};

const deleting = ref(null);
const deleteForm = useForm({});
const confirmDelete = () => {
    if (!deleting.value) return;
    deleteForm.delete(route('items.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleting.value = null;
        },
        onError: () => {},
    });
};

let draggingRow = null;
const onRowDragStart = (event, row) => {
    draggingRow = row;
    event.dataTransfer.effectAllowed = 'move';
};

const onRowDrop = (row) => {
    if (!draggingRow || draggingRow.id === row.id) return;
    const from = rows.value.findIndex((r) => r.id === draggingRow.id);
    const to = rows.value.findIndex((r) => r.id === row.id);
    if (from < 0 || to < 0) return;

    const [moved] = rows.value.splice(from, 1);
    rows.value.splice(to, 0, moved);
    draggingRow = null;

    router.post(
        route('items.reorder'),
        { items: rows.value.map((r) => r.id) },
        { preserveScroll: true },
    );
};

const formatPrice = (price) => {
    const value = parseFloat(price);
    return Number.isNaN(value) ? price : value.toLocaleString();
};
</script>

<template>
    <Head title="الأصناف" />

    <RestaurantLayout>
        <PageHeader
            title="الأصناف"
            subtitle="أدر قائمة الأصناف الخاصة بمطعمك وأسعارها وحالتها."
        >
            <template #actions>
                <Button variant="primary" @click="openCreate">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    صنف جديد
                </Button>
            </template>
        </PageHeader>

        <Card>
            <div class="grid gap-4 border-b border-slate-100 p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800">
                <div class="sm:col-span-2">
                    <SearchInput v-model="search" placeholder="ابحث عن صنف..." />
                </div>
                <Select
                    v-model="selectedCategory"
                    :options="[
                        { value: '', label: 'كل الأقسام' },
                        ...categories.map((c) => ({ value: c.id, label: translate(c.name) })),
                    ]"
                />
                <Select
                    v-model="availability"
                    :options="[
                        { value: '', label: 'كل الحالات' },
                        { value: 'available', label: 'متاح' },
                        { value: 'unavailable', label: 'غير متاح' },
                    ]"
                />
            </div>

            <EmptyState
                v-if="!rows.length"
                :title="hasFilters() ? 'لا توجد نتائج' : 'لا توجد أصناف بعد'"
                :description="hasFilters() ? 'جرّب تغيير عوامل البحث أو إعادة تعيينها.' : 'ابدأ بإضافة أول صنف لقائمتك.'"
            >
                <template #action>
                    <div class="flex items-center justify-center gap-3">
                        <Button v-if="hasFilters()" variant="secondary" @click="resetFilters">
                            إعادة التعيين
                        </Button>
                        <Button v-if="!hasFilters()" variant="primary" @click="openCreate">
                            <template #icon>
                                <Icon name="plus" class="h-4 w-4" />
                            </template>
                            صنف جديد
                        </Button>
                    </div>
                </template>
            </EmptyState>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="w-8 px-4 py-3"></th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                            >
                                الصنف
                            </th>
                            <th
                                class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 md:table-cell dark:text-slate-400"
                            >
                                القسم
                            </th>
                            <th
                                class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 md:table-cell dark:text-slate-400"
                            >
                                السعر
                            </th>
                            <th
                                class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 lg:table-cell dark:text-slate-400"
                            >
                                علامات
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                            >
                                متاح
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 dark:text-slate-400"
                            >
                                إجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="item in rows"
                            :key="item.id"
                            class="cursor-grab transition hover:bg-slate-50 active:cursor-grabbing dark:hover:bg-slate-800/50"
                            draggable="true"
                            @dragstart="onRowDragStart($event, item)"
                            @dragover.prevent
                            @drop="onRowDrop(item)"
                        >
                            <td class="px-4 py-3 text-slate-300 dark:text-slate-600">
                                <Icon name="grip" class="h-4 w-4" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800"
                                    >
                                        <img
                                            v-if="item.image"
                                            :src="item.image"
                                            :alt="translate(item.name)"
                                            class="h-full w-full object-cover"
                                        />
                                        <Icon v-else name="image" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ translate(item.name) }}
                                        </p>
                                        <p class="truncate text-xs text-slate-400">
                                            {{ translate(item.description) || '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <Badge v-if="item.category_name" variant="neutral">
                                    {{ translate(item.category_name) }}
                                </Badge>
                                <span v-else class="text-xs text-slate-400">بدون قسم</span>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                        {{ formatPrice(item.price) }}
                                    </span>
                                    <span
                                        v-if="item.compare_at_price"
                                        class="text-xs text-slate-400 line-through"
                                    >
                                        {{ formatPrice(item.compare_at_price) }}
                                    </span>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 lg:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-if="item.is_featured" variant="warning">مميز</Badge>
                                    <Badge v-if="item.is_popular" variant="danger">الأكثر طلباً</Badge>
                                    <Badge v-if="item.is_new" variant="brand">جديد</Badge>
                                    <span v-if="!item.is_featured && !item.is_popular && !item.is_new" class="text-xs text-slate-400">
                                        —
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Toggle
                                    :model-value="item.is_available"
                                    @update:model-value="toggleField(item, 'is_available', $event)"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="secondary"
                                        size="sm"
                                        @click="openEdit(item)"
                                    >
                                        <template #icon>
                                            <Icon name="edit" class="h-3.5 w-3.5" />
                                        </template>
                                        تعديل
                                    </Button>
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        @click="deleting = item"
                                    >
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
                v-if="items.links && items.links.length > 3"
                :links="items.links"
                class="border-t border-slate-100 p-4 dark:border-slate-800"
            />
        </Card>

        <Dialog
            :show="showForm"
            :title="editing ? 'تعديل الصنف' : 'صنف جديد'"
            @close="showForm = false"
        >
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <InputLabel value="الاسم" />
                    <LocaleTabs v-model="formLocale" />
                    <div :dir="formLocale === 'en' ? 'ltr' : 'rtl'">
                        <TextInput
                            v-if="formLocale === 'ar'"
                            v-model="form.name.ar"
                            type="text"
                            placeholder="مثال: شاورما دجاج"
                            class="mt-2 w-full"
                        />
                        <TextInput
                            v-else
                            v-model="form.name.en"
                            type="text"
                            placeholder="e.g. Chicken Shawarma"
                            class="mt-2 w-full"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors['name.' + formLocale]" />
                </div>

                <div>
                    <InputLabel value="الوصف" />
                    <LocaleTabs v-model="formLocale" />
                    <div :dir="formLocale === 'en' ? 'ltr' : 'rtl'">
                        <Textarea
                            v-if="formLocale === 'ar'"
                            v-model="form.description.ar"
                            placeholder="وصف اختياري للصنف..."
                            class="mt-2 w-full"
                        />
                        <Textarea
                            v-else
                            v-model="form.description.en"
                            placeholder="Optional description..."
                            class="mt-2 w-full"
                        />
                    </div>
                    <InputError class="mt-1" :message="form.errors['description.' + formLocale]" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="القسم" />
                        <Select
                            v-model="form.menu_category_id"
                            :options="[
                                { value: '', label: 'بدون قسم' },
                                ...categories.map((c) => ({ value: c.id, label: translate(c.name) })),
                            ]"
                            class="mt-2 w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.menu_category_id" />
                    </div>
                    <div>
                        <InputLabel value="السعر" />
                        <TextInput
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            dir="ltr"
                            class="mt-2 w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.price" />
                    </div>
                    <div>
                        <InputLabel value="سعر قبل التخفيض" />
                        <TextInput
                            v-model="form.compare_at_price"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            dir="ltr"
                            class="mt-2 w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.compare_at_price" />
                    </div>
                </div>

                <div>
                    <InputLabel value="صورة الصنف" />
                    <ImageUploader
                        :current="editing?.image"
                        class="mt-2"
                        @update:file="onImage"
                    />
                    <InputError class="mt-1" :message="form.errors.image" />
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">متاح للطلب</p>
                        <Toggle v-model="form.is_available" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">مميز</p>
                        <Toggle v-model="form.is_featured" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">الأكثر طلباً</p>
                        <Toggle v-model="form.is_popular" />
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">جديد</p>
                        <Toggle v-model="form.is_new" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <Button variant="ghost" @click="showForm = false">إلغاء</Button>
                    <Button
                        variant="primary"
                        type="submit"
                        :loading="form.processing"
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
            title="حذف الصنف"
            :message="deleting ? 'هل أنت متأكد من حذف صنف «' + translate(deleting.name) + '»؟' : ''"
            confirm-label="حذف"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </RestaurantLayout>
</template>