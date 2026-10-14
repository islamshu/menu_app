/**
 * Shared branding helpers for the menu themes and the branding studio.
 *
 * Mirrors the small subset of `App\Support\Color` that the frontend needs so
 * the published palette and the live preview compute identical tokens. Kept
 * dependency-free and framework-agnostic.
 */

export function hexToRgb(hex) {
    if (typeof hex !== 'string') {
        return null;
    }

    let value = hex.trim().replace('#', '');

    if (value.length === 3) {
        value = value
            .split('')
            .map((char) => char + char)
            .join('');
    }

    if (!/^[0-9a-fA-F]{6}$/.test(value)) {
        return null;
    }

    return [
        parseInt(value.slice(0, 2), 16),
        parseInt(value.slice(2, 4), 16),
        parseInt(value.slice(4, 6), 16),
    ];
}

export function rgbToHex(rgb) {
    return `#${rgb
        .map((channel) => Math.max(0, Math.min(255, Math.round(channel))))
        .map((channel) => channel.toString(16).padStart(2, '0'))
        .join('')}`;
}

export function luminance(rgb) {
    const channels = (rgb ?? [0, 0, 0]).map((channel) => {
        const value = channel / 255;

        return value <= 0.03928
            ? value / 12.92
            : ((value + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
}

export function contrastRatio(a, b) {
    const l1 = luminance(a);
    const l2 = luminance(b);
    const light = Math.max(l1, l2);
    const dark = Math.min(l1, l2);

    return (light + 0.05) / (dark + 0.05);
}

export function isLight(hex) {
    return luminance(hexToRgb(hex) ?? [255, 255, 255]) > 0.5;
}

export function bestTextColor(hex) {
    const rgb = hexToRgb(hex) ?? [255, 255, 255];
    const light = [248, 246, 241];
    const dark = [17, 17, 20];

    return contrastRatio(rgb, light) >= contrastRatio(rgb, dark)
        ? rgbToHex(light)
        : rgbToHex(dark);
}

export function mix(a, b, t) {
    const rgbA = hexToRgb(a);
    const rgbB = hexToRgb(b);

    if (!rgbA || !rgbB) {
        return a;
    }

    const ratio = Math.max(0, Math.min(1, t));

    return rgbToHex(
        rgbA.map((channel, index) => channel + (rgbB[index] - channel) * ratio),
    );
}

/**
 * A surface tint derived from the background (used for cards & headers).
 */
export function surfaceFor(background) {
    return mix(background, isLight(background) ? '#000000' : '#ffffff', 0.06);
}

export function borderFor(background) {
    return mix(background, isLight(background) ? '#000000' : '#ffffff', 0.14);
}

export function mutedFor(text, background) {
    return mix(text, background, 0.42);
}

/**
 * Resolve the CSS font stack for a font key from the shared options payload.
 */
export function resolveFontStack(options, key) {
    const font = (options?.fonts ?? []).find((entry) => entry.value === key);

    if (font?.stack) {
        return font.stack;
    }

    return "system-ui, 'IBM Plex Sans Arabic', sans-serif";
}

export function resolveRadius(options, key) {
    const radius = (options?.radii ?? []).find((entry) => entry.value === key);

    return radius?.css ?? '0px';
}

/**
 * Build a single bunny.net request for the fonts actually in use.
 */
export function googleFontsUrl(config, keys) {
    const families = [...new Set((keys ?? []).filter(Boolean))]
        .map((key) => (config?.fonts ?? []).find((entry) => entry.value === key)?.bunny)
        .filter(Boolean);

    if (!families.length) {
        return null;
    }

    return `https://fonts.bunny.net/css?family=${families.join('|')}&display=swap`;
}

/**
 * Translate a resolved branding settings object into CSS custom properties.
 */
export function buildTokens(settings, options) {
    const background = settings?.background_color ?? '#ffffff';
    const text = settings?.text_color ?? '#111111';
    const primary = settings?.primary_color ?? '#111111';

    return {
        '--brand-primary': primary,
        '--brand-secondary': settings?.secondary_color ?? primary,
        '--brand-accent': settings?.accent_color ?? primary,
        '--brand-bg': background,
        '--brand-surface': surfaceFor(background),
        '--brand-border': borderFor(background),
        '--brand-text': text,
        '--brand-muted': mutedFor(text, background),
        '--brand-on-primary': bestTextColor(primary),
        '--brand-on-bg': bestTextColor(background),
        '--brand-radius': resolveRadius(options, settings?.border_radius),
        '--brand-font-heading': resolveFontStack(options, settings?.heading_font),
        '--brand-font-body': resolveFontStack(options, settings?.body_font),
    };
}
