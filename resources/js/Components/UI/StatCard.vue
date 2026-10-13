<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: 0,
    },
    hint: {
        type: String,
        default: null,
    },
    tone: {
        type: String,
        default: 'brand',
    },
});

const tones = {
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
    emerald:
        'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    rose: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
    slate: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
};
</script>

<template>
    <div
        class="rounded-xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900 dark:shadow-card-dark"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ label }}
                </p>
                <p
                    class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100"
                >
                    {{ value }}
                </p>
                <p
                    v-if="hint"
                    class="mt-1 text-xs text-slate-400 dark:text-slate-500"
                >
                    {{ hint }}
                </p>
            </div>

            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                :class="tones[tone] ?? tones.brand"
            >
                <slot name="icon" />
            </div>
        </div>
    </div>
</template>
