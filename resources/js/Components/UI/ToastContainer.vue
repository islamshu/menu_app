<script setup>
import { useToasts } from '@/Composables/useToasts';

const { toasts, dismiss } = useToasts();

const tones = {
    success: {
        bar: 'bg-emerald-500',
        icon: 'text-emerald-500',
        path: 'M5 13l4 4L19 7',
    },
    error: {
        bar: 'bg-rose-500',
        icon: 'text-rose-500',
        path: 'M6 18L18 6M6 6l12 12',
    },
    info: {
        bar: 'bg-brand-500',
        icon: 'text-brand-500',
        path: 'M12 8h.01M11 12h1v4h1',
    },
    warning: {
        bar: 'bg-amber-500',
        icon: 'text-amber-500',
        path: 'M12 9v4m0 4h.01',
    },
};
</script>

<template>
    <div
        class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-pop dark:border-slate-800 dark:bg-slate-900"
            >
                <span
                    class="mt-1 h-2 w-2 shrink-0 rounded-full"
                    :class="(tones[toast.type] ?? tones.info).bar"
                />

                <p class="flex-1 text-sm text-slate-700 dark:text-slate-200">
                    {{ toast.message }}
                </p>

                <button
                    type="button"
                    class="rounded p-1 text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200"
                    aria-label="إغلاق"
                    @click="dismiss(toast.id)"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
