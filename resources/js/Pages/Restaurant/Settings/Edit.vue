<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Icon from '@/Components/UI/Icon.vue';
import ImageUploader from '@/Components/UI/ImageUploader.vue';
import MenuQrCode from '@/Components/UI/MenuQrCode.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import { submitMultipart } from '@/Support/forms';

const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    currencies: {
        type: Array,
        required: true,
    },
    timezones: {
        type: Array,
        required: true,
    },
    locales: {
        type: Array,
        required: true,
    },
    menu_url: {
        type: String,
        default: '',
    },
});

const activeSection = ref('general');

const form = useForm({
    name: props.restaurant.name,
    description: props.restaurant.description ?? '',
    phone: props.restaurant.phone ?? '',
    email: props.restaurant.email ?? '',
    address: props.restaurant.address ?? '',
    currency: props.restaurant.currency,
    default_locale: props.restaurant.default_locale,
    timezone: props.restaurant.timezone,
    logo: null,
    remove_logo: false,
    cover_image: null,
    remove_cover: false,
});

const onLogo = (file) => {
    form.logo = file;
    if (file) {
        form.remove_logo = false;
    } else if (props.restaurant.logo) {
        form.remove_logo = true;
    }
};

const onCover = (file) => {
    form.cover_image = file;
    if (file) {
        form.remove_cover = false;
    } else if (props.restaurant.cover_image) {
        form.remove_cover = true;
    }
};

const submit = () => {
    submitMultipart(form, 'put', route('settings.update'));
};

const sections = [
    { id: 'general', label: 'المعلومات الأساسية', icon: 'store' },
    { id: 'branding', label: 'العلامة التجارية', icon: 'image' },
    { id: 'preferences', label: 'التفضيلات', icon: 'cog' },
    { id: 'qr', label: 'رمز QR', icon: 'qr-code' },
];
</script>

<template>
    <Head title="إعدادات المطعم" />

    <RestaurantLayout>
        <PageHeader
            :title="restaurant.name"
            subtitle="إدارة معلومات المطعم والعلامة التجارية والتفضيلات."
        >
            <template #actions>
                <Badge
                    :variant="restaurant.status === 'active' ? 'success' : 'neutral'"
                    dot
                >
                    {{ restaurant.status_label }}
                </Badge>
            </template>
        </PageHeader>

        <form class="grid grid-cols-1 gap-6 lg:grid-cols-4" @submit.prevent="submit">
            <div class="lg:col-span-1">
                <Card class="lg:sticky lg:top-20">
                    <nav class="flex flex-row gap-1 overflow-x-auto lg:flex-col">
                        <button
                            v-for="section in sections"
                            :key="section.id"
                            type="button"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                            :class="
                                activeSection === section.id
                                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'
                            "
                            @click="activeSection = section.id"
                        >
                            <Icon :name="section.icon" class="h-4 w-4" />
                            {{ section.label }}
                        </button>
                    </nav>
                </Card>
            </div>

            <div class="space-y-6 lg:col-span-3">
                <Card v-show="activeSection === 'general'" title="المعلومات الأساسية">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel value="اسم المطعم" />
                            <TextInput
                                v-model="form.name"
                                type="text"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.name" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel value="نبذة عن المطعم" />
                            <Textarea
                                v-model="form.description"
                                class="mt-2 w-full"
                                placeholder="وصف قصير يظهر في صفحة المطعم..."
                            />
                            <InputError class="mt-1" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel value="رقم الهاتف" />
                            <TextInput
                                v-model="form.phone"
                                type="tel"
                                dir="ltr"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.phone" />
                        </div>

                        <div>
                            <InputLabel value="البريد الإلكتروني" />
                            <TextInput
                                v-model="form.email"
                                type="email"
                                dir="ltr"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.email" />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel value="العنوان" />
                            <TextInput
                                v-model="form.address"
                                type="text"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.address" />
                        </div>
                    </div>
                </Card>

                <Card v-show="activeSection === 'branding'" title="العلامة التجارية">
                    <p class="mb-5 text-sm text-slate-500 dark:text-slate-400">
                        صور المطعم تظهر في صفحتك الرقمية وتطبيقات المشاركة.
                    </p>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel value="شعار المطعم" />
                            <ImageUploader
                                :current="restaurant.logo"
                                class="mt-2"
                                @update:file="onLogo"
                            />
                            <InputError class="mt-1" :message="form.errors.logo" />
                        </div>

                        <div>
                            <InputLabel value="صورة الغلاف" />
                            <ImageUploader
                                :current="restaurant.cover_image"
                                class="mt-2"
                                @update:file="onCover"
                            />
                            <InputError class="mt-1" :message="form.errors.cover_image" />
                        </div>
                    </div>
                </Card>

                <Card v-show="activeSection === 'preferences'" title="التفضيلات">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <InputLabel value="العملة" />
                            <Select
                                v-model="form.currency"
                                :options="currencies.map((c) => ({ value: c.code, label: c.label }))"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.currency" />
                        </div>

                        <div>
                            <InputLabel value="اللغة الافتراضية" />
                            <Select
                                v-model="form.default_locale"
                                :options="locales.map((l) => ({ value: l.code, label: l.label }))"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.default_locale" />
                        </div>

                        <div>
                            <InputLabel value="المنطقة الزمنية" />
                            <Select
                                v-model="form.timezone"
                                :options="timezones.map((t) => ({ value: t, label: t }))"
                                class="mt-2 w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.timezone" />
                        </div>
                    </div>
                </Card>

                <Card v-show="activeSection === 'qr'" title="رمز QR للقائمة">
                    <MenuQrCode :url="menu_url" :name="restaurant.name" />
                </Card>

                <div class="flex items-center justify-end gap-3">
                    <Button
                        variant="primary"
                        type="submit"
                        :loading="form.processing"
                        :disabled="!form.isDirty && !form.logo && !form.cover_image"
                    >
                        <template #icon>
                            <Icon name="check" class="h-4 w-4" />
                        </template>
                        حفظ التغييرات
                    </Button>
                </div>
            </div>
        </form>
    </RestaurantLayout>
</template>