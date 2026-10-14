<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <Head title="Digital Menu Platform" />

    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-6 text-center"
    >
        <span
            class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-400"
        >
            Digital Menu SaaS
        </span>

        <h1 class="mt-4 text-3xl font-semibold text-gray-900 sm:text-4xl">
            Beautiful digital menus for restaurants and cafes
        </h1>

        <p class="mt-4 max-w-xl text-base text-gray-600">
            This is a placeholder landing page. The public digital menu, smart
            branding and QR studio will be implemented in the next phase.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <template v-if="$page.props.auth.user">
                <Link
                    :href="route('dashboard')"
                    class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-700"
                >
                    Go to Dashboard
                </Link>
            </template>

            <template v-else>
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-700"
                >
                    Log in
                </Link>
            </template>
        </div>
    </div>
</template>
