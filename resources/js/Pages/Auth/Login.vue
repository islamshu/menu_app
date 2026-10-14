<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout
        title="تسجيل الدخول"
        subtitle="أدخل بياناتك للوصول إلى لوحة التحكم"
    >
        <Head title="تسجيل الدخول" />

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

            <div>
                <div class="flex items-center justify-between">
                    <label
                        for="password"
                        class="block text-sm font-medium text-slate-700 dark:text-slate-200"
                    >
                        كلمة المرور
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                    >
                        نسيت كلمة المرور؟
                    </Link>
                </div>

                <div class="relative mt-1.5">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="pe-10"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        :error="form.errors.password"
                    />

                    <button
                        type="button"
                        class="absolute inset-y-0 end-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 focus:outline-none dark:hover:text-slate-200"
                        :aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                        @click="showPassword = !showPassword"
                    >
                        <svg
                            v-if="showPassword"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M9.9 4.7A9.9 9.9 0 0 1 12 4.5c6 0 9.5 7.5 9.5 7.5a17.6 17.6 0 0 1-3 3.9M6.3 6.3A17.5 17.5 0 0 0 2.5 12s3.5 7.5 9.5 7.5a9.7 9.7 0 0 0 4-0.9"
                            />
                            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                            <path d="M3 3l18 18" />
                        </svg>

                        <svg
                            v-else
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M2.5 12S6 4.5 12 4.5 21.5 12 21.5 12 18 19.5 12 19.5 2.5 12 2.5 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex w-fit items-center gap-2">
                <Checkbox
                    name="remember"
                    v-model:checked="form.remember"
                />
                <span class="text-sm text-slate-600 dark:text-slate-300">
                    تذكرني
                </span>
            </label>

            <Button
                type="submit"
                size="lg"
                block
                :loading="form.processing"
            >
                دخول
            </Button>
        </form>

        <p
            class="mt-8 text-center text-xs leading-relaxed text-slate-400 dark:text-slate-500"
        >
            الوصول متاح للحسابات المُنشأة من قِبل الإدارة فقط.
        </p>
    </GuestLayout>
</template>
