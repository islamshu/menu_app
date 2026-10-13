<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: null,
    },
    maxWidth: {
        type: String,
        default: 'lg',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const widths = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
};

const widthClass = computed(() => widths[props.maxWidth] ?? widths.lg);

const close = () => {
    if (props.closeable) emit('close');
};

const onKey = (event) => {
    if (event.key === 'Escape' && props.show) close();
};

watch(
    () => props.show,
    (value) => {
        if (typeof document === 'undefined') return;
        document.body.style.overflow = value ? 'hidden' : '';
    },
);

onMounted(() => document.addEventListener('keydown', onKey));

onUnmounted(() => {
    document.removeEventListener('keydown', onKey);
    if (typeof document !== 'undefined') document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="show"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                    @click="close"
                />

                <div
                    class="relative flex min-h-full items-end justify-center p-4 sm:items-center"
                >
                    <div
                        v-show="show"
                        role="dialog"
                        aria-modal="true"
                        :class="widthClass"
                        class="w-full animate-slide-up rounded-2xl border border-slate-200 bg-white shadow-pop dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            v-if="title || $slots.header"
                            class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-800"
                        >
                            <slot name="header">
                                <h2
                                    class="text-base font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    {{ title }}
                                </h2>
                            </slot>

                            <button
                                v-if="closeable"
                                type="button"
                                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                                aria-label="إغلاق"
                                @click="close"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M6 6l12 12M18 6L6 18"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="px-5 py-5">
                            <slot />
                        </div>

                        <div
                            v-if="$slots.footer"
                            class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-4 dark:border-slate-800"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
