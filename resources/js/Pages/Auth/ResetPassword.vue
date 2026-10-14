<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout
        title="إعادة تعيين كلمة المرور"
        subtitle="اختر كلمة مرور جديدة لحسابك"
    >
        <Head title="إعادة تعيين كلمة المرور" />

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
                    required
                    autocomplete="username"
                    :error="form.errors.email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-200"
                >
                    كلمة المرور الجديدة
                </label>

                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1.5"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                    :error="form.errors.password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-200"
                >
                    تأكيد كلمة المرور
                </label>

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1.5"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                    :error="form.errors.password_confirmation"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <Button type="submit" size="lg" block :loading="form.processing">
                إعادة تعيين كلمة المرور
            </Button>
        </form>
    </GuestLayout>
</template>
