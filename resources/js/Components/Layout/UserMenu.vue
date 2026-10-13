<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuItem from '@/Components/UI/MenuItem.vue';

const page = usePage();

const user = computed(() => page.props.auth.user);

const initials = computed(() => {
    const name = user.value?.name ?? '';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
});
</script>

<template>
    <Dropdown
        align="right"
        width="48"
        content-classes="py-1 bg-white dark:bg-slate-900"
    >
        <template #trigger>
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg p-1 transition hover:bg-slate-100 dark:hover:bg-slate-800"
                aria-haspopup="true"
            >
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white"
                >
                    {{ initials }}
                </span>
                <Icon name="chevron-down" class="h-4 w-4 text-slate-400" />
            </button>
        </template>

        <template #content>
            <div class="border-b border-slate-100 px-3 py-2 dark:border-slate-800">
                <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">
                    {{ user?.name }}
                </p>
                <p class="truncate text-xs text-slate-400">
                    {{ user?.email }}
                </p>
            </div>

            <MenuItem :href="route('profile.edit')">
                <Icon name="cog" class="h-4 w-4 text-slate-400" />
                الملف الشخصي
            </MenuItem>

            <MenuItem :href="route('logout')" method="post" as="button" danger>
                <Icon name="logout" class="h-4 w-4" />
                تسجيل الخروج
            </MenuItem>
        </template>
    </Dropdown>
</template>
