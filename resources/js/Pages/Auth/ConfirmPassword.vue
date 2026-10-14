<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout
        title="تأكيد كلمة المرور"
        subtitle="منطقة آمنة، يرجى تأكيد كلمة المرور للمتابعة"
    >
        <Head title="تأكيد كلمة المرور" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-200"
                >
                    كلمة المرور
                </label>

                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1.5"
                    placeholder="••••••••"
                    required
                    autofocus
                    autocomplete="current-password"
                    :error="form.errors.password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <Button type="submit" size="lg" block :loading="form.processing">
                تأكيد
            </Button>
        </form>
    </GuestLayout>
</template>
