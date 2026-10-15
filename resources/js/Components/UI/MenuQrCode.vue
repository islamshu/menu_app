<script setup>
import { computed } from 'vue';
import Button from '@/Components/UI/Button.vue';
import Icon from '@/Components/UI/Icon.vue';
import { downloadDataUrl, printQrImage } from '@/Support/qr';

const props = defineProps({
    url: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        default: '',
    },
    image: {
        type: String,
        default: '',
    },
});

const filename = computed(() => {
    const slug = (props.name || 'menu')
        .trim()
        .replace(/[^\p{L}\p{N}]+/gu, '-')
        .replace(/^-+|-+$/g, '')
        .toLowerCase();

    return `qr-${slug || 'menu'}`;
});

const downloadPng = () => {
    downloadDataUrl(props.image, `${filename.value}.png`);
};

const print = () => {
    printQrImage(props.image, { title: props.name, subtitle: props.url });
};
</script>

<template>
    <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-center">
        <div
            class="shrink-0 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700"
        >
            <img v-if="image" :src="image" :alt="`QR ${name}`" class="h-40 w-40" />
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm text-slate-600 dark:text-slate-300">
                امسح الرمز بكاميرا الهاتف لفتح قائمة المطعم مباشرة.
            </p>
            <p class="mt-1 truncate text-xs text-slate-400" dir="ltr">{{ url }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                <Button variant="secondary" size="sm" @click="print">
                    <template #icon>
                        <Icon name="printer" class="h-3.5 w-3.5" />
                    </template>
                    طباعة
                </Button>
                <Button variant="outline" size="sm" :disabled="!image" @click="downloadPng">
                    <template #icon>
                        <Icon name="download" class="h-3.5 w-3.5" />
                    </template>
                    تحميل PNG
                </Button>
            </div>
        </div>
    </div>
</template>
