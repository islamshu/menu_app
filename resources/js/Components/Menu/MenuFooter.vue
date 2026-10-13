<script setup>
const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    locale: {
        type: String,
        required: true,
    },
});

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-ink-line px-5 py-12 text-center">
        <p class="font-display text-lg text-ink">
            {{ restaurant.name }}
        </p>

        <p class="mt-3 text-xs text-ink-faint">
            {{
                locale === 'ar'
                    ? 'هذه القائمة للعرض فقط — لا تتضمن طلباً أو دفعاً إلكترونياً.'
                    : 'This menu is for display only — no ordering or online payment.'
            }}
        </p>

        <p class="mt-6 text-[11px] uppercase tracking-[0.3em] text-ink-faint">
            {{ year }}
        </p>
    </footer>
</template>
