import { ref, watch } from 'vue';

const isDark = ref(false);

if (typeof document !== 'undefined') {
    isDark.value = document.documentElement.classList.contains('dark');
}

watch(
    isDark,
    (value) => {
        if (typeof document === 'undefined') return;

        document.documentElement.classList.toggle('dark', value);

        try {
            localStorage.setItem('theme', value ? 'dark' : 'light');
        } catch (e) {
            /* storage unavailable */
        }
    },
    { immediate: true },
);

export function useTheme() {
    const toggle = () => {
        isDark.value = !isDark.value;
    };

    return { isDark, toggle };
}
