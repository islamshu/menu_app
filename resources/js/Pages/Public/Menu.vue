<script setup>
import MenuExperience from '@/Components/Menu/MenuExperience.vue';

defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    featured: {
        type: Array,
        default: () => [],
    },
    branding: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <MenuExperience
        :restaurant="restaurant"
        :categories="categories"
        :featured="featured"
        :branding="branding"
        :meta="meta"
    />
</template>
