import { defineAsyncComponent } from 'vue';

/**
 * Lazy theme map. Each entry is code-split so a public menu only downloads the
 * theme it actually renders, keeping the initial payload small.
 */
const loaders = {
    'noir-luxe': () => import('./noir-luxe/NoirLuxeTheme.vue'),
    atelier: () => import('./atelier/AtelierTheme.vue'),
    pulse: () => import('./pulse/PulseTheme.vue'),
    mosaic: () => import('./mosaic/MosaicTheme.vue'),
    bistro: () => import('./bistro/BistroTheme.vue'),
    neon: () => import('./neon/NeonTheme.vue'),
    orchard: () => import('./orchard/OrchardTheme.vue'),
};

export const themeKeys = Object.keys(loaders);

const cache = {};

export function resolveTheme(key, fallback = 'noir-luxe') {
    const resolved = loaders[key] ? key : fallback;

    if (!cache[resolved]) {
        cache[resolved] = defineAsyncComponent(loaders[resolved]);
    }

    return cache[resolved];
}
