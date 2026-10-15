<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import Icon from '@/Components/UI/Icon.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import QrPreview from '@/Components/Qr/QrPreview.vue';
import { qrArtworkSvg, downloadDataUrl, downloadTextFile, imageToDataUrl, printQrCode, rasterizeSvgToPng } from '@/Support/qr';
import { useToasts } from '@/Composables/useToasts';
import { contrastRatio, hexToRgb } from '@/Support/branding';

const props = defineProps({
    restaurant: { type: Object, required: true },
    menu_url: { type: String, required: true },
    presets: { type: Array, required: true },
    options: { type: Object, required: true },
    published: { type: Object, required: true },
    draft: { type: Object, required: true },
    has_draft: { type: Boolean, default: false },
});

const KEYS = [
    'preset_key',
    'error_level',
    'module_style',
    'eye_style',
    'frame_style',
    'quiet_zone',
    'foreground_color',
    'background_color',
    'eye_color',
    'frame_label',
    'logo_enabled',
    'logo_ratio',
];

const { success, error } = useToasts();

const form = useForm({
    preset_key: props.draft.preset_key || 'noir-luxe',
    error_level: props.draft.error_level || 'Q',
    module_style: props.draft.module_style || 'rounded',
    eye_style: props.draft.eye_style || 'rounded',
    frame_style: props.draft.frame_style || 'card',
    quiet_zone: props.draft.quiet_zone ?? 4,
    foreground_color: props.draft.foreground_color || '#000000',
    background_color: props.draft.background_color || '#ffffff',
    eye_color: props.draft.eye_color || props.draft.foreground_color || '#000000',
    frame_label: props.draft.frame_label || props.restaurant.name || '',
    logo_enabled: props.draft.logo_enabled || false,
    logo_ratio: props.draft.logo_ratio || 0.12,
});

const confirmDiscard = ref(false);
const discarding = ref(false);
const exporting = ref(false);

const selectedPreset = computed(() => props.presets.find((p) => p.key === form.preset_key) || props.presets[0]);

const logoDataUrl = ref('');

watch(
    () => [form.logo_enabled, props.restaurant.logo],
    async ([enabled]) => {
        if (enabled && props.restaurant.logo) {
            logoDataUrl.value = await imageToDataUrl(props.restaurant.logo);
        } else {
            logoDataUrl.value = '';
        }
    },
    { immediate: true },
);

const artwork = computed(() => {
    return qrArtworkSvg(props.menu_url, form.data(), {
        label: form.frame_label || props.restaurant.name,
        logo: form.logo_enabled ? logoDataUrl.value : '',
    });
});

const MIN_QR_CONTRAST = 3.5;

const contrastWarn = computed(() => {
    const fg = hexToRgb(form.foreground_color);
    const bg = hexToRgb(form.background_color);
    if (!fg || !bg) return false;
    return contrastRatio(fg, bg) < MIN_QR_CONTRAST;
});

const eyeContrastWarn = computed(() => {
    const eye = hexToRgb(form.eye_color);
    const bg = hexToRgb(form.background_color);
    if (!eye || !bg) return false;
    return contrastRatio(eye, bg) < MIN_QR_CONTRAST;
});

const filename = computed(() => {
    const slug = (props.restaurant.name || 'qr').trim().replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-+|-+$/g, '').toLowerCase();
    return `qr-${slug || 'menu'}`;
});

const applyPreset = (key) => {
    const p = props.presets.find((x) => x.key === key);
    if (!p) return;
    form.preset_key = key;
    KEYS.forEach((k) => {
        if (p.defaults[k] !== undefined) form[k] = p.defaults[k];
    });
};

const saveDraft = () => {
    form.put(route('qr.update'), { preserveScroll: true });
};

const publish = () => {
    form.post(route('qr.publish'), { preserveScroll: true });
};

const discard = () => {
    discarding.value = true;
    router.delete(route('qr.discard'), {
        preserveScroll: true,
        onSuccess: () => { confirmDiscard.value = false; success('تم تجاهل المسودة'); },
        onFinish: () => { discarding.value = false; },
    });
};

const downloadPng = async () => {
    exporting.value = true;
    try {
        const dataUrl = await rasterizeSvgToPng(artwork.value, 1024);
        downloadDataUrl(dataUrl, `${filename.value}.png`);
    } catch (e) {
        error('فشل في تصدير PNG');
    } finally {
        exporting.value = false;
    }
};

const downloadSvg = () => {
    downloadTextFile(artwork.value, `${filename.value}.svg`, 'image/svg+xml');
};

const print = () => {
    printQrCode(artwork.value, { title: props.restaurant.name, subtitle: props.menu_url });
};
</script>

<template>
    <Head title="استوديو رمز QR" />
    <RestaurantLayout>
        <PageHeader title="استوديو رمز QR" subtitle="تخصيص رمز QR لقائمة المطعم: الألوان، شكل الوحدات، عين الرمز، الإطار، والخطأ التصحيحي">
            <template #actions>
                <Badge v-if="has_draft" variant="warning" dot>مسودة غير منشورة</Badge>
                <a :href="menu_url" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    <Icon name="eye" class="h-4 w-4" />
                    معاينة القائمة
                </a>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,380px)_1fr]">
            <div class="space-y-6">
                <Card title="المعاينة الحيّة">
                    <QrPreview :svg="artwork" :url="menu_url" :name="restaurant.name" />
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button variant="secondary" size="sm" @click="print">طباعة</Button>
                        <Button variant="outline" size="sm" @click="downloadSvg">تحميل SVG</Button>
                        <Button variant="outline" size="sm" :loading="exporting" @click="downloadPng">تحميل PNG</Button>
                    </div>
                </Card>

                <Card title="الأوضاع الجاهزة">
                    <div class="grid grid-cols-1 gap-2">
                        <button v-for="p in presets" :key="p.key" type="button" @click="applyPreset(p.key)"
                            class="flex items-center gap-3 rounded-lg border p-2 text-start transition"
                            :class="form.preset_key === p.key ? 'border-brand-500 ring-2 ring-brand-500/30' : 'border-slate-200 hover:border-brand-400'">
                            <span class="text-sm font-medium">{{ p.label.ar }} · {{ p.label.en }}</span>
                        </button>
                    </div>
                </Card>
            </div>

            <div class="space-y-6">
                <Card title="التصميم">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="مستوى تصحيح الخطأ" />
                            <Select v-model="form.error_level" class="mt-2 w-full" :options="options.error_levels" />
                            <InputError class="mt-1" :message="form.errors.error_level" />
                        </div>
                        <div>
                            <InputLabel value="شكل وحدات الرمز (Module Style)" />
                            <Select v-model="form.module_style" class="mt-2 w-full" :options="options.module_styles" />
                            <InputError class="mt-1" :message="form.errors.module_style" />
                        </div>
                        <div>
                            <InputLabel value="شكل عين الرمز (Eye Style)" />
                            <Select v-model="form.eye_style" class="mt-2 w-full" :options="options.eye_styles" />
                            <InputError class="mt-1" :message="form.errors.eye_style" />
                        </div>
                        <div>
                            <InputLabel value="نمط الإطار (Frame Style)" />
                            <Select v-model="form.frame_style" class="mt-2 w-full" :options="options.frame_styles" />
                            <InputError class="mt-1" :message="form.errors.frame_style" />
                        </div>
                        <div>
                            <InputLabel value="مساحة هادئة (Quiet Zone)" />
                            <TextInput type="number" min="0" max="8" v-model="form.quiet_zone" dir="ltr" />
                            <InputError class="mt-1" :message="form.errors.quiet_zone" />
                        </div>
                        <div v-if="form.frame_style !== 'none'">
                            <InputLabel value="نص الإطار (Frame Label)" />
                            <TextInput v-model="form.frame_label" maxlength="60" />
                            <InputError class="mt-1" :message="form.errors.frame_label" />
                        </div>
                    </div>
                </Card>

                <Card title="الألوان">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="لون الوحدات (Foreground)" />
                            <div class="mt-2 flex gap-2"><input type="color" v-model="form.foreground_color" class="h-9 w-12 rounded border" /><TextInput v-model="form.foreground_color" dir="ltr" class="flex-1 font-mono uppercase" /></div>
                            <InputError class="mt-1" :message="form.errors.foreground_color" />
                        </div>
                        <div>
                            <InputLabel value="لون الخلفية (Background)" />
                            <div class="mt-2 flex gap-2"><input type="color" v-model="form.background_color" class="h-9 w-12 rounded border" /><TextInput v-model="form.background_color" dir="ltr" class="flex-1 font-mono uppercase" /></div>
                            <InputError class="mt-1" :message="form.errors.background_color" />
                        </div>
                        <div>
                            <InputLabel value="لون عين الرمز (Eye)" />
                            <div class="mt-2 flex gap-2"><input type="color" v-model="form.eye_color" class="h-9 w-12 rounded border" /><TextInput v-model="form.eye_color" dir="ltr" class="flex-1 font-mono uppercase" /></div>
                            <InputError class="mt-1" :message="form.errors.eye_color" />
                            <p v-if="eyeContrastWarn" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                لون العين فاتح جداً؛ سيتم استخدام لون الوحدات للعين تلقائياً لضمان قراءة الرمز.
                            </p>
                        </div>
                    </div>
                    <Badge v-if="contrastWarn" variant="warning" class="mt-3">تباين منخفض بين لون الوحدات وخلفية الرمز قد يؤثر على قراءة بعض أجهزة المسح</Badge>
                </Card>

                <Card title="شعار المطعم داخل الرمز">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="إدراج شعار المطعم" />
                            <label class="mt-2 flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="checkbox" v-model="form.logo_enabled" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                                استخدام شعار المطعم
                            </label>
                            <p v-if="!restaurant.logo" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                لا يوجد شعار للمطعم. أضِفه من الإعدادات أولاً.
                            </p>
                            <InputError class="mt-1" :message="form.errors.logo_enabled" />
                        </div>
                        <div v-if="form.logo_enabled">
                            <InputLabel value="حجم الشعار (Logo Ratio)" />
                            <TextInput type="number" min="0.08" max="0.15" step="0.01" v-model="form.logo_ratio" dir="ltr" />
                            <InputError class="mt-1" :message="form.errors.logo_ratio" />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                عند إدراج الشعار يتم رفع مستوى تصحيح الخطأ تلقائياً إلى الأقصى (H) لضمان قراءة الرمز.
                            </p>
                        </div>
                    </div>
                </Card>

                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" :loading="form.processing" :disabled="!form.isDirty" @click="saveDraft">حفظ كمسودة</Button>
                    <Button variant="primary" :loading="form.processing" @click="publish">نشر الإعدادات</Button>
                    <Button v-if="has_draft" variant="danger-ghost" @click="confirmDiscard = true">تجاهل المسودة</Button>
                </div>
            </div>
        </div>

        <ConfirmDialog :show="confirmDiscard" title="تجاهل المسودة" message="سيتم حذف التغييرات غير المنشورة والعودة إلى الإعدادات المنشورة حالياً." confirm-label="تجاهل المسودة" :processing="discarding" @confirm="discard" @close="confirmDiscard = false" />
    </RestaurantLayout>
</template>
