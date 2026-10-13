import { ref } from 'vue';

const toasts = ref([]);
let counter = 0;

export function useToasts() {
    const dismiss = (id) => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    };

    const push = (message, type = 'success', timeout = 4000) => {
        if (!message) return null;

        const id = ++counter;
        toasts.value.push({ id, message, type });

        if (timeout) {
            setTimeout(() => dismiss(id), timeout);
        }

        return id;
    };

    return {
        toasts,
        push,
        dismiss,
        success: (message) => push(message, 'success'),
        error: (message) => push(message, 'error'),
        info: (message) => push(message, 'info'),
        warning: (message) => push(message, 'warning'),
    };
}
