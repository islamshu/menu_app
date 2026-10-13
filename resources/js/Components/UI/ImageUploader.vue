<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import Button from './Button.vue';

const props = defineProps({
    label: {
        type: String,
        default: 'الصورة',
    },
    current: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    accept: {
        type: String,
        default: 'image/png,image/jpeg,image/webp',
    },
    hint: {
        type: String,
        default: 'PNG أو JPG أو WebP — بحد أقصى 2MB',
    },
});

const emit = defineEmits(['update:file']);

const input = ref(null);
const preview = ref(null);
const dragging = ref(false);
const removed = ref(false);

const shown = computed(() => {
    if (removed.value) return null;
    return preview.value ?? props.current;
});

const pick = () => input.value?.click();

const setFile = (file) => {
    if (preview.value) URL.revokeObjectURL(preview.value);

    if (file) {
        preview.value = URL.createObjectURL(file);
        removed.value = false;
        emit('update:file', file);
    } else {
        preview.value = null;
        removed.value = Boolean(props.current);
        emit('update:file', null);
    }
};

const onChange = (event) => {
    setFile(event.target.files?.[0] ?? null);
    event.target.value = '';
};

const onDrop = (event) => {
    dragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) setFile(file);
};

const clear = () => setFile(null);

onBeforeUnmount(() => {
    if (preview.value) URL.revokeObjectURL(preview.value);
});
</script>

<template>
    <div>
        <div
            class="relative flex cursor-pointer items-center gap-4 rounded-xl border p-3 transition"
            :class="
                dragging
                    ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/10'
                    : error
                      ? 'border-rose-400 dark:border-rose-500/60'
                      : 'border-dashed border-slate-300 hover:border-brand-400 dark:border-slate-700'
            "
            @click="pick"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <div
                class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800"
            >
                <img
                    v-if="shown"
                    :src="shown"
                    :alt="label"
                    class="h-full w-full object-cover"
                />
                <svg
                    v-else
                    class="h-7 w-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 8.25h.008v.008H18V8.25zM3.75 4.5h16.5A1.5 1.5 0 0121.75 6v12a1.5 1.5 0 01-1.5 1.5H3.75A1.5 1.5 0 012.25 18V6a1.5 1.5 0 011.5-1.5z"
                    />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    {{ label }}
                </p>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                    {{ hint }}
                </p>

                <div class="mt-2 flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        type="button"
                        @click.stop="pick"
                    >
                        اختيار صورة
                    </Button>
                    <Button
                        v-if="shown"
                        variant="danger-ghost"
                        size="sm"
                        type="button"
                        @click.stop="clear"
                    >
                        إزالة
                    </Button>
                </div>
            </div>

            <input
                ref="input"
                type="file"
                class="hidden"
                :accept="accept"
                @change="onChange"
            />
        </div>
    </div>
</template>
