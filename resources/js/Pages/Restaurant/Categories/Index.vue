<script setup>
import { ref, watch } from 'vue';
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
import TextInput from '@/Components/UI/TextInput.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Toggle from '@/Components/UI/Toggle.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import { translate } from '@/Support/i18n';

const props = defineProps({
    categories: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const search = ref(props.filters.search ?? '');
watch(search, (value) => {
    router.get(
        route('categories.index'),
        { search: value, page: 1 },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const rows = ref([]);
watch(
    () => props.categories.data,
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
    is_active: true,
    image: null,
    remove_image: false,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    formLocale.value = 'ar';
    showForm.value = true;
};

const openEdit = (category) => {
    editing.value = category;
    form.name = {
        ar: category.name?.ar ?? '',
        en: category.name?.en ?? '',
    };
    form.description = {
        ar: category.description?.ar ?? '',
        en: category.description?.en ?? '',
    };
    form.is_active = category.is_active;
    form.image = null;
    form.remove_image = false;
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
        forceFormData: true,
        onSuccess: () => {
            showForm.value = false;
            editing.value = null;
        },
    };

    if (editing.value) {
        form.put(route('categories.update', editing.value.id), options);
    } else {
        form.post(route('categories.store'), options);
    }
};

const toggleActive = (category, value) => {
    router.patch(
        route('categories.toggle', category.id),
        { field: 'is_active', value },
        { preserveScroll: true },
    );
};

const deleting = ref(null);
const deleteForm = useForm({});
const confirmDelete = () => {
    if (!deleting.value) return;
    deleteForm.delete(route('categories.destroy', deleting.value.id), {
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
    persistOrder();
};

const persistOrder = () => {
    router.post(
        route('categories.reorder'),
        { items: rows.value.map((r) => r.id) },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="الأقسام" />

    <RestaurantLayout>
        <PageHeader
            title="الأقسام"
            subtitle="نظّم قائمتك إلى أقسام ليسهل على الزبائن التصفح."
        >
            <template #actions>
                <Button variant="primary" @click="openCreate">
                    <template #icon>
                        <Icon name="plus" class="h-4 w-4" />
                    </template>
                    قسم جديد
                </Button>
            </template>
        </PageHeader>

        <Card>
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-4 dark:border-slate-800">
                <div class="w-full max-w-sm">
                    <SearchInput v-model="search" placeholder="ابحث عن قسم..." />
                </div>
                <span class="hidden text-sm text-slate-400 sm:block">
                    {{ categories.total ?? rows.length }} قسم
                </span>
            </div>

            <EmptyState
                v-if="!rows.length"
                :title="search ? 'لا توجد نتائج' : 'لا توجد أقسام بعد'"
                :description="search ? 'جرّب كلمة بحث أخرى.' : 'ابدأ بإنشاء أول قسم لقائمتك.'"
            >
                <template #action>
                    <Button v-if="!search" variant="primary" @click="openCreate">
                        <template #icon>
                            <Icon name="plus" class="h-4 w-4" />
                        </template>
                        قسم جديد
                    </Button>
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
                                الاسم
                            </th>
                            <th
                                class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 lg:table-cell dark:text-slate-400"
                            >
                                الأصناف
                            </th>
                            <th
                                class="hidden px-4 py-3 text-right text-xs font-medium uppercase text-slate-500 md:table-cell dark:text-slate-400"
                            >
                                الظهور
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
                            v-for="category in rows"
                            :key="category.id"
                            class="cursor-grab transition hover:bg-slate-50 active:cursor-grabbing dark:hover:bg-slate-800/50"
                            draggable="true"
                            @dragstart="onRowDragStart($event, category)"
                            @dragover.prevent
                            @drop="onRowDrop(category)"
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
                                            v-if="category.image"
                                            :src="category.image"
                                            :alt="translate(category.name)"
                                            class="h-full w-full object-cover"
                                        />
                                        <Icon v-else name="folder" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ translate(category.name) }}
                                        </p>
                                        <p class="truncate text-xs text-slate-400">
                                            {{ translate(category.description) || '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="hidden px-4 py-3 text-sm text-slate-600 lg:table-cell dark:text-slate-300"
                            >
                                <Badge variant="neutral">
                                    {{ category.items_count }}
                                </Badge>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <Toggle
                                    :model-value="category.is_active"
                                    @update:model-value="toggleActive(category, $event)"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="secondary"
                                        size="sm"
                                        @click="openEdit(category)"
                                    >
                                        <template #icon>
                                            <Icon name="edit" class="h-3.5 w-3.5" />
                                        </template>
                                        تعديل
                                    </Button>
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        @click="deleting = category"
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
                v-if="categories.links && categories.links.length > 3"
                :links="categories.links"
                class="border-t border-slate-100 p-4 dark:border-slate-800"
            />
        </Card>

        <Dialog
            :show="showForm"
            :title="editing ? 'تعديل القسم' : 'قسم جديد'"
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
                            placeholder="مثال: المقبلات"
                            class="mt-2 w-full"
                        />
                        <TextInput
                            v-else
                            v-model="form.name.en"
                            type="text"
                            placeholder="e.g. Appetizers"
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
                            placeholder="وصف اختياري للقسم..."
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

                <div>
                    <InputLabel value="صورة القسم" />
                    <ImageUploader
                        :current="editing?.image"
                        class="mt-2"
                        @update:file="onImage"
                    />
                    <InputError class="mt-1" :message="form.errors.image" />
                </div>

                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 dark:bg-slate-800/50">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">ظاهر للزوار</p>
                        <p class="text-xs text-slate-400">اعرض هذا القسم في قائمتك الرقمية.</p>
                    </div>
                    <Toggle v-model="form.is_active" />
                </div>

                <div v-if="form.processing" class="text-xs text-slate-400">جارٍ الحفظ...</div>

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
            title="حذف القسم"
            :message="deleting ? 'هل أنت متأكد من حذف قسم «' + translate(deleting.name) + '»؟ ستبقى الأصناف التابعة له بدون قسم.' : ''"
            confirm-label="حذف"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </RestaurantLayout>
</template>