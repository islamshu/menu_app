<script setup>
import { computed } from 'vue';
import AppLogo from '@/Components/Shared/AppLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const year = computed(() => new Date().getFullYear());

const features = [
    'قوائم رقمية جاهزة للمشاركة برمز QR',
    'هوية بصرية وثيمات قابلة للتخصيص',
    'إدارة الأصناف والأسعار والعروض بسهولة',
];
</script>

<template>
    <div
        class="min-h-screen bg-slate-50 lg:grid lg:grid-cols-2 dark:bg-slate-950"
    >
        <aside
            class="relative hidden overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 lg:flex lg:flex-col lg:justify-between lg:p-12"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.12]"
                style="
                    background-image: radial-gradient(
                        circle at 1px 1px,
                        #fff 1px,
                        transparent 0
                    );
                    background-size: 28px 28px;
                "
            />
            <div
                class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -right-16 -bottom-24 h-72 w-72 rounded-full bg-brand-400/20 blur-3xl"
            />

            <Link href="/" class="relative flex items-center gap-3 text-white">
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20 backdrop-blur"
                >
                    <AppLogo class="h-6 w-6" />
                </span>
                <span class="text-lg font-semibold tracking-tight">
                    منصة القوائم الرقمية
                </span>
            </Link>

            <div class="relative max-w-md text-white">
                <h1
                    class="font-display text-3xl font-semibold leading-snug"
                >
                    قوائم رقمية أنيقة لمطاعمك ومقاهيك
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/80">
                    أنشئ قائمة طعام جميلة، خصّص هويتها البصرية، وأدر أصنافك
                    وأسعارك من مكان واحد.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-white/90">
                    <li
                        v-for="feature in features"
                        :key="feature"
                        class="flex items-center gap-3"
                    >
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/20"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        {{ feature }}
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-white/60">
                © {{ year }} منصة القوائم الرقمية. جميع الحقوق محفوظة.
            </p>
        </aside>

        <main class="flex min-h-screen flex-col justify-center px-6 py-12 sm:px-10">
            <div class="mx-auto w-full max-w-sm">
                <Link
                    href="/"
                    class="mb-10 flex items-center justify-center gap-3 lg:hidden"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-sm"
                    >
                        <AppLogo class="h-5 w-5" />
                    </span>
                    <span
                        class="text-base font-semibold tracking-tight text-slate-900 dark:text-white"
                    >
                        منصة القوائم الرقمية
                    </span>
                </Link>

                <header v-if="title" class="mb-8 text-center lg:text-start">
                    <h2
                        class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ title }}
                    </h2>
                    <p
                        v-if="subtitle"
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        {{ subtitle }}
                    </p>
                </header>

                <slot />
            </div>
        </main>
    </div>
</template>
