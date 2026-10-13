<script setup>
import Button from './Button.vue';
import Dialog from './Dialog.vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'تأكيد الحذف',
    },
    message: {
        type: String,
        default: 'هل أنت متأكد؟ لا يمكن التراجع عن هذا الإجراء.',
    },
    confirmLabel: {
        type: String,
        default: 'حذف',
    },
    variant: {
        type: String,
        default: 'danger',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['confirm', 'close']);
</script>

<template>
    <Dialog
        :show="show"
        :title="title"
        max-width="md"
        :closeable="!processing"
        @close="$emit('close')"
    >
        <p class="text-sm text-slate-600 dark:text-slate-300">
            {{ message }}
        </p>

        <template #footer>
            <Button
                variant="outline"
                :disabled="processing"
                @click="$emit('close')"
            >
                إلغاء
            </Button>
            <Button
                :variant="variant"
                :loading="processing"
                @click="$emit('confirm')"
            >
                {{ confirmLabel }}
            </Button>
        </template>
    </Dialog>
</template>
