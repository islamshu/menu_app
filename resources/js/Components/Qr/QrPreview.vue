<template>
    <div class="flex flex-col items-center gap-4" dir="ltr">
        <div class="max-w-[320px] rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700" v-html="svg" />
        <div class="text-xs text-slate-500 truncate max-w-full" dir="ltr">{{ url }}</div>
    </div>
</template>

<script setup>
defineProps({
    svg: { type: String, default: '' },
    url: { type: String, default: '' },
    name: { type: String, default: '' },
});
</script>
