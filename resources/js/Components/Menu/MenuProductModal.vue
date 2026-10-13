<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Icon from '@/Components/UI/Icon.vue';
import MenuBadge from './MenuBadge.vue';
import { formatMoney, hasDiscount, translate } from '@/Support/menu';

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
    locale: {
        type: String,
        required: true,
    },
    currency: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close']);

const panel = ref(null);
const closeButton = ref(null);
let lastFocused = null;

const name = computed(() => translate(props.item?.name, props.locale));
const description = computed(() =>
    translate(props.item?.description, props.locale),
);

const onKeydown = (event) => {
    if (!props.item) {
        return;
    }

    if (event.key === 'Escape') {
        event.preventDefault();
        emit('close');

        return;
    }

    if (event.key !== 'Tab' || !panel.value) {
        return;
    }

    const focusable = panel.value.querySelectorAll(
        'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])',
    );

    if (!focusable.length) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
};

watch(
    () => props.item,
    (value) => {
        if (typeof document === 'undefined') {
            return;
        }

        if (value) {
            lastFocused = document.activeElement;
            document.body.style.overflow = 'hidden';
            nextTick(() => closeButton.value?.focus());
        } else {
            document.body.style.overflow = '';
            lastFocused?.focus?.();
        }
    },
);

onMounted(() => document.addEventListener('keydown', onKeydown));

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="item"
                class="fixed inset-0 z-[60] overflow-y-auto"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="`dish-${item.id}-title`"
            >
                <div
                    class="fixed inset-0 bg-ink/70 backdrop-blur-sm"
                    @click="emit('close')"
                />

                <div
                    class="relative flex min-h-full items-end justify-center sm:items-center sm:p-6"
                >
                    <div
                        ref="panel"
                        class="relative w-full max-w-lg animate-fade-up overflow-hidden rounded-t-3xl bg-paper shadow-editor motion-reduce:animate-none sm:rounded-3xl"
                    >
                        <button
                            ref="closeButton"
                            type="button"
                            class="absolute end-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-ink/60 text-paper backdrop-blur transition hover:bg-ink/80 focus-visible:ring-2 focus-visible:ring-paper"
                            aria-label="إغلاق"
                            @click="emit('close')"
                        >
                            <Icon name="x" class="h-4 w-4" />
                        </button>

                        <div class="relative aspect-[16/10] bg-cream">
                            <img
                                v-if="item.image"
                                :src="item.image"
                                :alt="name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-ember-100 to-cream"
                                aria-hidden="true"
                            >
                                <span
                                    class="font-display text-6xl text-ember-300/80"
                                >
                                    {{ name.charAt(0) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-4 p-6">
                            <div
                                v-if="
                                    item.is_featured ||
                                    item.is_popular ||
                                    item.is_new
                                "
                                class="flex flex-wrap gap-1.5"
                            >
                                <MenuBadge
                                    v-if="item.is_featured"
                                    variant="featured"
                                    :label="
                                        locale === 'ar' ? 'مميز' : 'Featured'
                                    "
                                />
                                <MenuBadge
                                    v-if="item.is_popular"
                                    variant="popular"
                                    :label="
                                        locale === 'ar'
                                            ? 'الأكثر طلباً'
                                            : 'Popular'
                                    "
                                />
                                <MenuBadge
                                    v-if="item.is_new"
                                    variant="new"
                                    :label="locale === 'ar' ? 'جديد' : 'New'"
                                />
                            </div>

                            <h2
                                :id="`dish-${item.id}-title`"
                                class="font-display text-2xl leading-tight text-ink"
                            >
                                {{ name }}
                            </h2>

                            <p
                                v-if="description"
                                class="text-sm leading-relaxed text-ink-muted"
                            >
                                {{ description }}
                            </p>

                            <div
                                class="flex items-center justify-between gap-4 border-t border-ink-line pt-4"
                            >
                                <div class="flex items-baseline gap-2">
                                    <span
                                        class="text-xl font-semibold text-ember-600"
                                    >
                                        {{
                                            formatMoney(
                                                item.price,
                                                currency,
                                                locale,
                                            )
                                        }}
                                    </span>
                                    <span
                                        v-if="hasDiscount(item)"
                                        class="text-sm text-ink-faint line-through"
                                    >
                                        {{
                                            formatMoney(
                                                item.compare_at_price,
                                                currency,
                                                locale,
                                            )
                                        }}
                                    </span>
                                </div>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                        aria-hidden="true"
                                    />
                                    {{
                                        locale === 'ar'
                                            ? 'متاح الآن'
                                            : 'Available now'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>