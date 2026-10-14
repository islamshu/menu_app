<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout
        title="تأكيد البريد الإلكتروني"
        subtitle="أرسلنا رابط تأكيد إلى بريدك، يرجى التحقق منه"
    >
        <Head title="تأكيد البريد الإلكتروني" />

        <div
            v-if="verificationLinkSent"
            class="mb-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
        >
            تم إرسال رابط تأكيد جديد إلى بريدك الإلكتروني.
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <Button type="submit" size="lg" block :loading="form.processing">
                إعادة إرسال رابط التأكيد
            </Button>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full text-center text-sm font-medium text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
            >
                تسجيل الخروج
            </Link>
        </form>
    </GuestLayout>
</template>
