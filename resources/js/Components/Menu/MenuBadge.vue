<script setup>
import { computed } from 'vue';
import Icon from '@/Components/UI/Icon.vue';

const props = defineProps({
    variant: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
});

const styles = {
    featured: {
        icon: 'star',
        class: 'bg-amber-400/95 text-ink',
    },
    popular: {
        icon: 'trending-up',
        class: 'bg-ember-600/95 text-white',
    },
    new: {
        icon: 'sparkles',
        class: 'bg-ink/90 text-paper',
    },
};

const current = computed(() => styles[props.variant] ?? styles.featured);
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold leading-none shadow-sm backdrop-blur-sm"
        :class="current.class"
    >
        <Icon :name="current.icon" class="h-3 w-3" />
        {{ label }}
    </span>
</template>
