<script setup>
defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <svg
        v-bind="$attrs"
        viewBox="0 0 48 48"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        aria-label="Digital Menu Platform"
    >
        <rect
            x="7"
            y="5"
            width="34"
            height="38"
            rx="5"
            stroke="currentColor"
            stroke-width="2"
        />
        <path
            d="M14 16h20M14 24h20M14 32h12"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
        />
    </svg>
</template>
