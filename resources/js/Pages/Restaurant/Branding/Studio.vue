<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import Icon from '@/Components/UI/Icon.vue';
import ImageUploader from '@/Components/UI/ImageUploader.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MenuExperience from '@/Components/Menu/MenuExperience.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Select from '@/Components/UI/Select.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import RestaurantLayout from '@/Layouts/RestaurantLayout.vue';
import { useToasts } from '@/Composables/useToasts';

const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    locales: {
        type: Array,
        required: true,
    },
    themes: {
        type: Array,
        required: true,
    },
    options: {
        type: Object,
        required: true,
    },
    published: {
        type: Object,
        required: true,
    },
    draft: {
        type: Object,
        required: true,
    },
    has_draft: {
        type: Boolean,
        default: false,
    },
    menu: {
        type: Object,
        required: true,
    },
    menu_url: {
        type: String,
        required: true,
    },
});

const BRANDING_KEYS = [
    'theme_key',
    'primary_color',
    'secondary_color',
    'accent_color',
    'background_color',
    'text_color',
    'heading_font',
    'body_font',
    'border_radius',
    'card_style',
    'logo_display_mode',
];

const { success, error, info } = useToasts();

const form = useForm({
    theme_key: props.draft.theme_key,
    primary_color: props.draft.primary_color,
    secondary_color: props.draft.secondary_color,
    accent_color: props.draft.accent_color,
    background_color: props.draft.background_color,
    text_color: props.draft.text_color,
    heading_font: props.draft.heading_font,
    body_font: props.draft.body_font,
    border_radius: props.draft.border_radius,
    card_style: props.draft.card_style,
    logo_display_mode: props.draft.logo_display_mode,
});

const colorFields = [
    { key: 'primary_color', label: 'اللون الأساسي' },
    { key: 'secondary_color', label: 'اللون الثانوي' },
    { key: 'accent_color', label: 'لون التمييز' },
    { key: 'background_color', label: 'لون الخلفية' },
    { key: 'text_color', label: 'لون النص' },
];

const colorOrder = [
    'primary_color',
    'secondary_color',
    'accent_color',
    'background_color',
    'text_color',
];

const applyPalette = (palette) => {
    colorOrder.forEach((key) => {
        if (palette.colors?.[key]) {
            form[key] = palette.colors[key];
        }
    });
};

const logoFile = ref(null);
const extracting = ref(false);
const confirmDiscard = ref(false);
const discarding = ref(false);

const selectedTheme = computed(
    () =>
        props.themes.find((theme) => theme.key === form.theme_key) ??
        props.themes[0],
);

const previewBranding = computed(() =>
    Object.fromEntries(BRANDING_KEYS.map((key) => [key, form[key]])),
);

const previewRestaurant = computed(() => ({
    ...props.restaurant,
    locales: props.locales,
}));

const previewMenu = computed(() => props.menu);

const applySettings = (settings) => {
    BRANDING_KEYS.forEach((key) => {
        if (settings[key] !== undefined) {
            form[key] = settings[key];
        }
    });
};

const selectTheme = (theme) => {
    applySettings(theme.defaults);
};

const resetTheme = () => selectTheme(selectedTheme.value);

const onLogo = (file) => {
    logoFile.value = file;
};

const extractPalette = async () => {
    extracting.value = true;

    try {
        const payload = new FormData();

        if (logoFile.value) {
            payload.append('logo', logoFile.value);
        }

        const { data } = await window.axios.post(
            route('branding.palette'),
            payload,
        );

        if (data?.available && data.palette) {
            form.primary_color = data.palette.primary;
            form.secondary_color = data.palette.secondary;
            form.accent_color = data.palette.accent;
            form.background_color = data.palette.background;
            form.text_color = data.palette.text;
            success('تم استخراج الألوان من الشعار وتطبيقها على المعاينة.');
        } else {
            info(data?.message ?? 'تعذّر تحليل الشعار تلقائياً.');
        }
    } catch (exception) {
        error(
            exception?.response?.data?.message ??
                'تعذّر تحليل الشعار تلقائياً. يمكنك اختيار الألوان يدوياً.',
        );
    } finally {
        extracting.value = false;
    }
};

const saveDraft = () => {
    form.put(route('branding.update'), { preserveScroll: true });
};

const publish = () => {
    form.post(route('branding.publish'), { preserveScroll: true });
};

const discard = () => {
    discarding.value = true;

    router.delete(route('branding.discard'), {
        preserveScroll: true,
        onSuccess: () => {
            applySettings(props.published);
            confirmDiscard.value = false;
        },
        onFinish: () => {
            discarding.value = false;
        },
    });
};
</script>

<template>
    <Head title="استوديو الهوية البصرية" />

    <RestaurantLayout>
        <PageHeader
            title="استوديو الهوية البصرية"
            subtitle="صمّم مظهر قائمتك الرقمية: الثيم، الألوان، الخطوط وشكل البطاقات."
        >
            <template #actions>
                <Badge v-if="has_draft" variant="warning" dot>
                    مسودة غير منشورة
                </Badge>
                <a
                    :href="menu_url"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <Icon name="eye" class="h-4 w-4" />
                    معاينة القائمة
                </a>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,380px)_1fr]">
            <div class="space-y-6">
                <Card title="الثيم">
                    <div class="grid grid-cols-1 gap-3">
                        <button
                            v-for="theme in themes"
                            :key="theme.key"
                            type="button"
                            class="flex items-center gap-3 rounded-xl border p-3 text-start transition"
                            :class="
                                form.theme_key === theme.key
                                    ? 'border-brand-500 ring-2 ring-brand-500/30'
                                    : 'border-slate-200 hover:border-brand-300 dark:border-slate-700'
                            "
                            @click="selectTheme(theme)"
                        >
                            <span
                                class="h-12 w-12 shrink-0 rounded-lg border border-black/5"
                                :style="{
                                    background: `linear-gradient(135deg, ${theme.preview.from}, ${theme.preview.to})`,
                                }"
                            />
                            <span class="min-w-0">
                                <span
                                    class="block text-sm font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    {{ theme.label.ar }} · {{ theme.label.en }}
                                </span>
                                <span
                                    class="mt-0.5 block text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                >
                                    {{ theme.description.ar }}
                                </span>
                            </span>
                            <Icon
                                v-if="form.theme_key === theme.key"
                                name="check-circle"
                                class="ms-auto h-5 w-5 shrink-0 text-brand-600"
                            />
                        </button>
                    </div>

                    <Button
                        variant="ghost"
                        size="sm"
                        class="mt-3"
                        type="button"
                        @click="resetTheme"
                    >
                        إعادة ضبط الثيم الافتراضي
                    </Button>
                </Card>

                <Card title="الألوان">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                استخرج لوحة ألوان تلقائية من شعار المطعم.
                            </p>
                            <Button
                                variant="secondary"
                                size="sm"
                                type="button"
                                :loading="extracting"
                                @click="extractPalette"
                            >
                                <template #icon>
                                    <Icon name="sparkles" class="h-4 w-4" />
                                </template>
                                استخراج تلقائي
                            </Button>
                        </div>

                        <ImageUploader
                            label="شعار لتحليل الألوان (اختياري)"
                            hint="استخدم الشعار الحالي أو ارفع صورة للتحليل فقط"
                            :current="restaurant.logo"
                            @update:file="onLogo"
                        />

                        <div v-if="options.palettes?.length">
                            <InputLabel value="لوحات ألوان جاهزة" />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                اختَر لوحة لتطبيقها فوراً على اللون الأساسي والثانوي والتمييز والخلفية والنص.
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <button
                                    v-for="palette in options.palettes"
                                    :key="palette.key"
                                    type="button"
                                    class="flex items-center gap-2 rounded-lg border border-slate-200 p-1.5 pe-2.5 text-start transition hover:border-brand-400 hover:ring-2 hover:ring-brand-500/20 dark:border-slate-700"
                                    :title="`${palette.label.ar} · ${palette.label.en}`"
                                    @click="applyPalette(palette)"
                                >
                                    <span
                                        class="flex overflow-hidden rounded-md ring-1 ring-black/5"
                                    >
                                        <span
                                            v-for="key in colorOrder"
                                            :key="key"
                                            class="h-6 w-3"
                                            :style="{
                                                backgroundColor:
                                                    palette.colors[key],
                                            }"
                                        />
                                    </span>
                                    <span
                                        class="text-xs text-slate-600 dark:text-slate-300"
                                    >
                                        {{ palette.label.ar }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div
                                v-for="field in colorFields"
                                :key="field.key"
                            >
                                <InputLabel :value="field.label" />
                                <div class="mt-2 flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="form[field.key]"
                                        class="h-9 w-12 shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-700"
                                        @input="
                                            form[field.key] = $event.target.value
                                        "
                                    />
                                    <TextInput
                                        v-model="form[field.key]"
                                        dir="ltr"
                                        class="flex-1 font-mono uppercase"
                                    />
                                </div>
                                <InputError
                                    class="mt-1"
                                    :message="form.errors[field.key]"
                                />
                            </div>
                        </div>
                    </div>
                </Card>

                <Card title="الخطوط والشكل">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="خط العناوين" />
                            <Select
                                v-model="form.heading_font"
                                class="mt-2 w-full"
                                :options="
                                    options.fonts.map((font) => ({
                                        value: font.value,
                                        label: font.label,
                                    }))
                                "
                            />
                            <InputError
                                class="mt-1"
                                :message="form.errors.heading_font"
                            />
                        </div>

                        <div>
                            <InputLabel value="خط النصوص" />
                            <Select
                                v-model="form.body_font"
                                class="mt-2 w-full"
                                :options="
                                    options.fonts.map((font) => ({
                                        value: font.value,
                                        label: font.label,
                                    }))
                                "
                            />
                            <InputError
                                class="mt-1"
                                :message="form.errors.body_font"
                            />
                        </div>

                        <div>
                            <InputLabel value="استدارة الحواف" />
                            <Select
                                v-model="form.border_radius"
                                class="mt-2 w-full"
                                :options="
                                    options.radii.map((radius) => ({
                                        value: radius.value,
                                        label: radius.label,
                                    }))
                                "
                            />
                            <InputError
                                class="mt-1"
                                :message="form.errors.border_radius"
                            />
                        </div>

                        <div>
                            <InputLabel value="شكل البطاقات" />
                            <Select
                                v-model="form.card_style"
                                class="mt-2 w-full"
                                :options="
                                    options.card_styles.map((style) => ({
                                        value: style.value,
                                        label: style.label,
                                    }))
                                "
                            />
                            <InputError
                                class="mt-1"
                                :message="form.errors.card_style"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <InputLabel value="طريقة عرض الشعار" />
                            <Select
                                v-model="form.logo_display_mode"
                                class="mt-2 w-full"
                                :options="
                                    options.logo_modes.map((mode) => ({
                                        value: mode.value,
                                        label: mode.label,
                                    }))
                                "
                            />
                            <InputError
                                class="mt-1"
                                :message="form.errors.logo_display_mode"
                            />
                        </div>
                    </div>
                </Card>
            </div>

            <div class="space-y-4">
                <Card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <Badge
                                :variant="has_draft ? 'warning' : 'success'"
                                dot
                            >
                                {{
                                    has_draft
                                        ? 'لديك مسودة غير منشورة'
                                        : 'الهوية المنشورة محدّثة'
                                }}
                            </Badge>
                            <span
                                v-if="form.isDirty"
                                class="text-xs font-medium text-amber-600 dark:text-amber-400"
                            >
                                تغييرات غير محفوظة
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                variant="outline"
                                type="button"
                                :loading="form.processing"
                                :disabled="!form.isDirty"
                                @click="saveDraft"
                            >
                                <template #icon>
                                    <Icon name="check" class="h-4 w-4" />
                                </template>
                                حفظ كمسودة
                            </Button>
                            <Button
                                variant="primary"
                                type="button"
                                :loading="form.processing"
                                @click="publish"
                            >
                                <template #icon>
                                    <Icon name="check-circle" class="h-4 w-4" />
                                </template>
                                نشر على القائمة
                            </Button>
                            <Button
                                v-if="has_draft"
                                variant="danger-ghost"
                                type="button"
                                :disabled="discarding"
                                @click="confirmDiscard = true"
                            >
                                <template #icon>
                                    <Icon name="trash" class="h-4 w-4" />
                                </template>
                                تجاهل المسودة
                            </Button>
                        </div>
                    </div>
                </Card>

            </div>

            <Card
                class="xl:col-span-2"
                title="المعاينة الحيّة"
            >
                <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                    معاينة مباشرة لشكل القائمة الرقمية كما ستظهر للزوار (بيانات
                    القائمة الفعلية، دون نشر التغييرات).
                </p>

                <div
                    class="h-[70vh] transform-gpu overflow-y-auto overscroll-contain rounded-xl border border-slate-200 bg-white dark:border-slate-800"
                >
                    <MenuExperience
                        :restaurant="previewRestaurant"
                        :categories="previewMenu.categories"
                        :featured="previewMenu.featured"
                        :branding="previewBranding"
                        preview
                    />
                </div>
            </Card>
        </div>

        <ConfirmDialog
            :show="confirmDiscard"
            title="تجاهل المسودة"
            message="سيتم حذف التغييرات غير المنشورة والعودة إلى الهوية المنشورة حالياً. لا يمكن التراجع عن هذا الإجراء."
            confirm-label="تجاهل المسودة"
            :processing="discarding"
            @confirm="discard"
            @close="confirmDiscard = false"
        />
    </RestaurantLayout>
</template>
