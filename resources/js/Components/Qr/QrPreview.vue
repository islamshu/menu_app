<template>
    <div class="flex flex-col items-center gap-4">
        <div
            class="qr-preview max-w-[320px] rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700"
            v-html="svg"
        ></div>
        <div class="w-full truncate text-center text-xs text-slate-500" dir="ltr">
            {{ url }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    svg: { type: String, default: '' },
    url: { type: String, default: '' },
    name: { type: String, default: '' },
});
</script>

<style scoped>
.qr-preview :deep(svg) {
    display: block;
    width: 100%;
    height: auto;
}
</style>
