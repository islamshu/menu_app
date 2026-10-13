<script setup>
defineProps({
    rounded: {
        type: String,
        default: 'rounded-lg',
    },
});
</script>

<template>
    <div class="skeleton" :class="rounded" />
</template>
