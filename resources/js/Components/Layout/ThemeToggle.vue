<script setup>
import { useTheme } from '@/Composables/useTheme';
import Icon from '@/Components/UI/Icon.vue';

const { isDark, toggle } = useTheme();
</script>

<template>
    <button
        type="button"
        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
        :aria-label="isDark ? 'الوضع النهاري' : 'الوضع الليلي'"
        @click="toggle"
    >
        <Icon :name="isDark ? 'sun' : 'moon'" class="h-5 w-5" />
    </button>
</template>
