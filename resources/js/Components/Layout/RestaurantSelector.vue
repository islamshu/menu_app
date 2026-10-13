<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuItem from '@/Components/UI/MenuItem.vue';

const page = usePage();

const active = computed(() => page.props.activeRestaurant);
const restaurants = computed(() => page.props.myRestaurants ?? []);
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
                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800"
                aria-haspopup="true"
            >
                <Icon name="building" class="h-4 w-4 text-slate-400" />
                <span
                    class="max-w-[10rem] truncate font-medium text-slate-700 dark:text-slate-200"
                >
                    {{ active ? active.name : 'اختر مطعماً' }}
                </span>
                <Icon name="chevron-down" class="h-4 w-4 text-slate-400" />
            </button>
        </template>

        <template #content>
            <div
                class="border-b border-slate-100 px-3 py-2 text-xs font-medium text-slate-400 dark:border-slate-800"
            >
                مطاعمي
            </div>

            <template v-if="restaurants.length">
                <MenuItem
                    v-for="restaurant in restaurants"
                    :key="restaurant.id"
                    :href="route('restaurants.switch', restaurant.id)"
                    method="post"
                >
                    <Icon
                        name="check"
                        class="h-4 w-4"
                        :class="
                            active && active.id === restaurant.id
                                ? 'text-brand-600'
                                : 'text-transparent'
                        "
                    />
                    <span class="flex-1 truncate">{{ restaurant.name }}</span>
                </MenuItem>
            </template>

            <p
                v-else
                class="px-3 py-2 text-sm text-slate-400"
            >
                لا توجد مطاعم
            </p>

            <div class="border-t border-slate-100 dark:border-slate-800">
                <MenuItem :href="route('restaurants.index')">
                    <Icon name="grid" class="h-4 w-4 text-slate-400" />
                    إدارة المطاعم
                </MenuItem>
            </div>
        </template>
    </Dropdown>
</template>
