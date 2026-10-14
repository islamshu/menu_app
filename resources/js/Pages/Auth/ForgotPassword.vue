<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout
        title="استعادة كلمة المرور"
        subtitle="سنرسل رابط إعادة التعيين إلى بريدك الإلكتروني"
    >
        <Head title="استعادة كلمة المرور" />

        <div
            v-if="status"
            class="mb-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
        >
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label
                    for="email"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-200"
                >
                    البريد الإلكتروني
                </label>

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1.5"
                    placeholder="name@example.com"
                    required
                    autofocus
                    autocomplete="username"
                    :error="form.errors.email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <Button type="submit" size="lg" block :loading="form.processing">
                إرسال رابط الاستعادة
            </Button>

            <Link
                :href="route('login')"
                class="block text-center text-sm font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
            >
                العودة لتسجيل الدخول
            </Link>
        </form>
    </GuestLayout>
</template>
