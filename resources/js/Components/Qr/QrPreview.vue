<script setup>
defineProps({
    image: { type: String, default: '' },
    url: { type: String, default: '' },
    name: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center gap-4">
        <div class="qr-preview max-w-[320px] rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700">
            <img v-if="image" :src="image" :alt="`QR ${name}`" class="h-auto w-full" />
        </div>
        <div class="w-full truncate text-center text-xs text-slate-400" dir="ltr">{{ url }}</div>
    </div>
</template>
