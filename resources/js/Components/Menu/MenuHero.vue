<script setup>
import Icon from '@/Components/UI/Icon.vue';
import { translate } from '@/Support/menu';

const props = defineProps({
    restaurant: {
        type: Object,
        required: true,
    },
    locale: {
        type: String,
        required: true,
    },
});

const hasContact = () =>
    props.restaurant.phone ||
    props.restaurant.email ||
    props.restaurant.address;

const description = () =>
    translate(props.restaurant.description, props.locale, props.restaurant.locale);
</script>

<template>
    <header class="relative">
        <div class="relative h-60 overflow-hidden sm:h-72 lg:h-80">
            <img
                v-if="restaurant.cover_image"
                :src="restaurant.cover_image"
                :alt="restaurant.name"
                class="h-full w-full origin-center animate-ken-burns object-cover motion-reduce:animate-none"
                fetchpriority="high"
            />
            <div
                v-else
                class="h-full w-full bg-gradient-to-br from-ember-800 via-ember-900 to-ink"
            />

            <div
                class="absolute inset-0 bg-gradient-to-t from-ink via-ink/60 to-ink/10"
            />
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.07]"
                style="
                    background-image: radial-gradient(
                        currentColor 1px,
                        transparent 1px
                    );
                    background-size: 22px 22px;
                "
            />
        </div>

        <div class="relative -mt-14 px-5 text-center">
            <div class="mx-auto flex max-w-2xl flex-col items-center">
                <span
                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-paper bg-cream shadow-editor sm:h-28 sm:w-28"
                >
                    <img
                        v-if="restaurant.logo"
                        :src="restaurant.logo"
                        :alt="restaurant.name"
                        class="h-full w-full object-cover"
                    />
                    <span
                        v-else
                        class="font-display text-4xl text-ember-600"
                    >
                        {{ restaurant.name.charAt(0) }}
                    </span>
                </span>

                <p
                    class="mt-5 text-[11px] font-semibold uppercase tracking-[0.42em] text-ember-600"
                >
                    {{ locale === 'ar' ? 'القائمة الرقمية' : 'Digital Menu' }}
                </p>

                <h1
                    class="mt-3 font-display text-4xl leading-tight text-ink sm:text-5xl"
                >
                    {{ restaurant.name }}
                </h1>

                <p
                    v-if="description()"
                    class="mt-4 max-w-xl text-sm leading-relaxed text-ink-muted sm:text-base"
                >
                    {{ description() }}
                </p>

                <div
                    v-if="hasContact()"
                    class="mt-7 flex flex-wrap items-center justify-center gap-2 text-xs sm:text-sm"
                >
                    <a
                        v-if="restaurant.phone"
                        :href="`tel:${restaurant.phone}`"
                        class="inline-flex items-center gap-2 rounded-full border border-ink-line bg-white px-3.5 py-2 text-ink-soft shadow-sm transition hover:-translate-y-0.5 hover:border-ember-300"
                        dir="ltr"
                    >
                        <Icon name="phone" class="h-4 w-4 text-ember-500" />
                        {{ restaurant.phone }}
                    </a>

                    <a
                        v-if="restaurant.email"
                        :href="`mailto:${restaurant.email}`"
                        class="inline-flex items-center gap-2 rounded-full border border-ink-line bg-white px-3.5 py-2 text-ink-soft shadow-sm transition hover:-translate-y-0.5 hover:border-ember-300"
                        dir="ltr"
                    >
                        <Icon name="mail" class="h-4 w-4 text-ember-500" />
                        {{ restaurant.email }}
                    </a>

                    <span
                        v-if="restaurant.address"
                        class="inline-flex items-center gap-2 rounded-full border border-ink-line bg-white px-3.5 py-2 text-ink-soft shadow-sm"
                    >
                        <Icon name="pin" class="h-4 w-4 text-ember-500" />
                        {{ restaurant.address }}
                    </span>
                </div>
            </div>
        </div>
    </header>
</template>
