<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import { translate } from '@/Support/menu';

const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    locale: {
        type: String,
        required: true,
    },
    locales: {
        type: Array,
        default: () => [],
    },
    activeId: {
        type: [Number, null],
        default: null,
    },
    search: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:search', 'update:locale', 'navigate']);

const scrolled = ref(false);
const mobileSearch = ref(false);

const onScroll = () => {
    scrolled.value = window.scrollY > 24;
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll);
});
</script>

<template>
    <div
        class="sticky top-0 z-40 border-b transition-colors duration-300"
        :class="
            scrolled
                ? 'border-ink-line bg-paper/90 shadow-[0_12px_30px_-26px_rgba(28,25,23,0.55)] backdrop-blur-md'
                : 'border-transparent bg-paper/70 backdrop-blur-sm'
        "
    >
        <div class="mx-auto max-w-6xl px-4">
            <div class="flex items-center gap-3 py-3">
                <a
                    href="#top"
                    class="flex min-w-0 items-center gap-2.5"
                    :aria-label="restaurant.name"
                >
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-ink-line bg-cream"
                    >
                        <img
                            v-if="restaurant.logo"
                            :src="restaurant.logo"
                            :alt="restaurant.name"
                            class="h-full w-full object-cover"
                        />
                        <span
                            v-else
                            class="font-display text-sm text-ember-600"
                        >
                            {{ restaurant.name.charAt(0) }}
                        </span>
                    </span>
                    <span
                        class="truncate font-display text-sm text-ink sm:text-base"
                    >
                        {{ restaurant.name }}
                    </span>
                </a>

                <div class="ms-auto hidden md:block">
                    <label class="relative block">
                        <span class="sr-only">
                            {{
                                locale === 'ar'
                                    ? 'البحث في القائمة'
                                    : 'Search the menu'
                            }}
                        </span>
                        <Icon
                            name="search"
                            class="pointer-events-none absolute inset-y-0 start-3 my-auto h-4 w-4 text-ink-faint"
                        />
                        <input
                            type="search"
                            :value="search"
                            :placeholder="
                                locale === 'ar'
                                    ? 'ابحث عن طبق...'
                                    : 'Search for a dish...'
                            "
                            class="w-56 rounded-full border border-ink-line bg-white py-2 ps-9 pe-4 text-sm text-ink placeholder:text-ink-faint focus:border-ember-400 focus:outline-none focus:ring-2 focus:ring-ember-400/40 lg:w-64"
                            @input="emit('update:search', $event.target.value)"
                        />
                    </label>
                </div>

                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-ink-line bg-white text-ink-soft transition hover:border-ember-300 md:hidden"
                    :aria-label="
                        locale === 'ar' ? 'البحث في القائمة' : 'Search the menu'
                    "
                    :aria-expanded="mobileSearch"
                    @click="mobileSearch = !mobileSearch"
                >
                    <Icon
                        :name="mobileSearch ? 'x' : 'search'"
                        class="h-4 w-4"
                    />
                </button>

                <div
                    class="flex items-center gap-0.5 rounded-full border border-ink-line bg-white p-0.5"
                    role="group"
                    :aria-label="
                        locale === 'ar' ? 'اختيار اللغة' : 'Select language'
                    "
                >
                    <button
                        v-for="option in locales"
                        :key="option.code"
                        type="button"
                        class="rounded-full px-2.5 py-1 text-xs font-semibold transition"
                        :class="
                            locale === option.code
                                ? 'bg-ink text-paper'
                                : 'text-ink-muted hover:text-ink'
                        "
                        :aria-pressed="locale === option.code"
                        @click="emit('update:locale', option.code)"
                    >
                        {{ option.code.toUpperCase() }}
                    </button>
                </div>
            </div>

            <div v-if="mobileSearch" class="pb-3 md:hidden">
                <label class="relative block">
                    <span class="sr-only">
                        {{
                            locale === 'ar'
                                ? 'البحث في القائمة'
                                : 'Search the menu'
                        }}
                    </span>
                    <Icon
                        name="search"
                        class="pointer-events-none absolute inset-y-0 start-3 my-auto h-4 w-4 text-ink-faint"
                    />
                    <input
                        type="search"
                        :value="search"
                        :placeholder="
                            locale === 'ar'
                                ? 'ابحث عن طبق...'
                                : 'Search for a dish...'
                        "
                        class="w-full rounded-full border border-ink-line bg-white py-2.5 ps-9 pe-4 text-sm text-ink placeholder:text-ink-faint focus:border-ember-400 focus:outline-none focus:ring-2 focus:ring-ember-400/40"
                        @input="emit('update:search', $event.target.value)"
                    />
                </label>
            </div>

            <nav
                v-if="categories.length && !search"
                class="scrollbar-thin flex gap-2 overflow-x-auto pb-3"
                :aria-label="
                    locale === 'ar' ? 'تصنيفات القائمة' : 'Menu categories'
                "
            >
                <button
                    v-for="category in categories"
                    :key="category.id"
                    type="button"
                    class="shrink-0 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-sm font-medium transition"
                    :class="
                        activeId === category.id
                            ? 'border-ink bg-ink text-paper'
                            : 'border-ink-line bg-white text-ink-soft hover:border-ember-300 hover:text-ink'
                    "
                    :aria-current="activeId === category.id ? 'true' : undefined"
                    @click="emit('navigate', category)"
                >
                    {{ translate(category.name, locale) }}
                </button>
            </nav>
        </div>
    </div>
</template>