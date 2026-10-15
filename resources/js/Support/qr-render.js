import { buildMatrix, escapeXml, isFinder, isTiming } from './qr-core.js';

function hexToRgb(hex) {
    if (typeof hex !== 'string') return [0, 0, 0];
    let v = hex.trim().replace('#', '');
    if (v.length === 3) {
        v = v.split('').map((x) => x + x).join('');
    }
    if (!/^[0-9a-fA-F]{6}$/.test(v)) return [0, 0, 0];
    return [parseInt(v.slice(0, 2), 16), parseInt(v.slice(2, 4), 16), parseInt(v.slice(4, 6), 16)];
}

function relativeLuminance(rgb) {
    const channel = (value) => {
        const c = value / 255;
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    };
    return 0.2126 * channel(rgb[0]) + 0.7152 * channel(rgb[1]) + 0.0722 * channel(rgb[2]);
}

function contrastRatio(a, b) {
    const l1 = relativeLuminance(a);
    const l2 = relativeLuminance(b);
    const light = Math.max(l1, l2);
    const dark = Math.min(l1, l2);
    return (light + 0.05) / (dark + 0.05);
}

// Scanners locate the QR through its three finder (eye) patterns, so the eye
// colour must contrast strongly with the background or the code becomes
// unreadable. Colours below this threshold fall back to the foreground/black.
const MIN_QR_CONTRAST = 3.5;

function ensureContrast(color, backgroundColor, fallback) {
    const bg = hexToRgb(backgroundColor);
    if (contrastRatio(hexToRgb(color), bg) >= MIN_QR_CONTRAST) {
        return color;
    }
    if (fallback && contrastRatio(hexToRgb(fallback), bg) >= MIN_QR_CONTRAST) {
        return fallback;
    }
    return relativeLuminance(bg) > 0.5 ? '#000000' : '#ffffff';
}

/**
 * The 1-based origin of each finder (eye) pattern: [row, col].
 * QR finders are always 7x7 at three corners.
 * @returns {Array<[number, number]>}
 */
function finderOrigins(count) {
    return [
        [0, 0],
        [0, count - 7],
        [count - 7, 0],
    ];
}

function finderOriginFor(count, r, c) {
    const origins = finderOrigins(count);
    for (const [or, oc] of origins) {
        if (r >= or && r < or + 7 && c >= oc && c < oc + 7) {
            return [or, oc];
        }
    }
    return null;
}

const LOGO_MIN_RATIO = 0.1;
const LOGO_MAX_RATIO = 0.22;
const LOGO_SAFE_LEVEL = 'H';

/**
 * A centre logo covers part of the QR, so it can only be recovered when the
 * error-correction level is high enough. Force the safest level and clamp the
 * logo size whenever a logo is present.
 */
function resolveLogoPolicy(hasLogo, errorLevel, logoRatio) {
    if (!hasLogo) {
        return { errorLevel, logoRatio: 0 };
    }

    const ratio = Math.min(LOGO_MAX_RATIO, Math.max(LOGO_MIN_RATIO, Number(logoRatio) || 0.18));

    return { errorLevel: LOGO_SAFE_LEVEL, logoRatio: ratio };
}

/**
 * Build one SVG string for the dark modules (excluding eyes) honouring the
 * requested module style, plus a separate string for the three eyes.
 */
function buildSvgShapes(count, modules, cellSize, m, { moduleStyle, eyeStyle, foregroundColor, eyeColor, logo }) {
    let normal = '';
    const eyeSet = new Set();
    for (const [or, oc] of finderOrigins(count)) {
        eyeSet.add(`${or}:${oc}`);
    }

    for (let r = 0; r < count; r++) {
        for (let c = 0; c < count; c++) {
            if (!modules[r][c]) continue;
            const origin = finderOriginFor(count, r, c);
            if (origin) continue; // eyes are drawn separately

            const x = m + c * cellSize;
            const y = m + r * cellSize;
            const cx = x + cellSize / 2;
            const cy = y + cellSize / 2;

            switch (moduleStyle) {
                case 'dots': {
                    normal += `<circle cx="${cx}" cy="${cy}" r="${(cellSize * 0.44).toFixed(2)}"/>`;
                    break;
                }
                case 'diamond': {
                    const t = cellSize * 0.06;
                    normal += `<path d="M${cx} ${y + t} L${x + cellSize - t} ${cy} L${cx} ${y + cellSize - t} L${x + t} ${cy} z"/>`;
                    break;
                }
                case 'rounded': {
                    normal += `<rect x="${x}" y="${y}" width="${cellSize}" height="${cellSize}" rx="${(cellSize * 0.32).toFixed(2)}" ry="${(cellSize * 0.32).toFixed(2)}"/>`;
                    break;
                }
                default: {
                    normal += `<rect x="${x}" y="${y}" width="${cellSize}" height="${cellSize}"/>`;
                }
            }
        }
    }

    let eyes = '';
    for (const [or, oc] of finderOrigins(count)) {
        const x = m + oc * cellSize;
        const y = m + or * cellSize;
        const s = cellSize;
        const size = 7 * s;
        const cx = x + size / 2;
        const cy = y + size / 2;

        if (eyeStyle === 'circle') {
            eyes += `<circle cx="${cx}" cy="${cy}" r="${(3 * s).toFixed(2)}" fill="none" stroke="${eyeColor}" stroke-width="${s}"/>`;
            eyes += `<circle cx="${cx}" cy="${cy}" r="${(1.5 * s).toFixed(2)}" fill="${eyeColor}"/>`;
        } else if (eyeStyle === 'rounded') {
            const outer = 1.6 * s;
            const inner = 0.8 * s;
            eyes += `<rect x="${x}" y="${y}" width="${size}" height="${size}" rx="${outer.toFixed(2)}" ry="${outer.toFixed(2)}" fill="none" stroke="${eyeColor}" stroke-width="${s}"/>`;
            eyes += `<rect x="${x + 2 * s}" y="${y + 2 * s}" width="${3 * s}" height="${3 * s}" rx="${inner.toFixed(2)}" ry="${inner.toFixed(2)}" fill="${eyeColor}"/>`;
        } else {
            eyes += `<rect x="${x}" y="${y}" width="${size}" height="${size}" fill="none" stroke="${eyeColor}" stroke-width="${s}"/>`;
            eyes += `<rect x="${x + 2 * s}" y="${y + 2 * s}" width="${3 * s}" height="${3 * s}" fill="${eyeColor}"/>`;
        }
    }

    return { normal, eyes };
}

/**
 * QR artwork SVG (QR + optional frame, label and logo).
 * @returns {{ svg: string, size: number }}
 */
function qrSvgInner(text, options = {}) {
    const {
        errorLevel = 'M',
        moduleStyle = 'square',
        eyeStyle = 'square',
        quietZone = 4,
        cellSize = 8,
        foregroundColor = '#000000',
        backgroundColor = '#ffffff',
        eyeColor = '#000000',
        logo = '',
        logoRatio = 0.18,
    } = options;

    const policy = resolveLogoPolicy(!!logo, errorLevel, logoRatio);
    const { count, modules } = buildMatrix(text, policy.errorLevel);
    const m = quietZone * cellSize;
    const size = count * cellSize + m * 2;

    const safeForeground = ensureContrast(foregroundColor, backgroundColor, null);
    const safeEye = ensureContrast(eyeColor, backgroundColor, safeForeground);

    const { normal, eyes } = buildSvgShapes(count, modules, cellSize, m, {
        moduleStyle,
        eyeStyle,
        foregroundColor: safeForeground,
        eyeColor: safeEye,
        logo,
    });

    let inner = `<rect x="0" y="0" width="${size}" height="${size}" fill="${backgroundColor}"/>`;
    inner += `<g fill="${safeForeground}">${normal}</g>`;
    inner += `<g>${eyes}</g>`;

    if (logo) {
        const logoSize = size * policy.logoRatio;
        const logoX = (size - logoSize) / 2;
        const pad = cellSize * 0.6;
        inner += `<rect x="${(logoX - pad).toFixed(2)}" y="${(logoX - pad).toFixed(2)}" width="${(logoSize + pad * 2).toFixed(2)}" height="${(logoSize + pad * 2).toFixed(2)}" rx="${(pad).toFixed(2)}" fill="${backgroundColor}"/>`;
        inner += `<image x="${logoX.toFixed(2)}" y="${logoX.toFixed(2)}" width="${logoSize.toFixed(2)}" height="${logoSize.toFixed(2)}" preserveAspectRatio="xMidYMid meet" href="${logo}"/>`;
    }

    return { inner, size, count };
}

/**
 * QR-only SVG (backwards compatible with the previous `qrSvg(text, opts)` shape).
 */
export function qrSvg(text, options = {}) {
    const {
        cellSize = 8,
        margin = 4,
        marginModules,
        quietZone,
        alt = '',
        title = '',
        scalable = true,
    } = options;

    const { inner, size } = qrSvgInner(text, {
        ...options,
        cellSize,
        quietZone: marginModules ?? quietZone ?? margin,
    });

    let svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg"';
    svg += !scalable ? ` width="${size}px" height="${size}px"` : '';
    svg += ` viewBox="0 0 ${size} ${size}" preserveAspectRatio="xMinYMin meet"`;
    if (title || alt) {
        svg += ' role="img"';
    }
    svg += '>';
    if (title) svg += `<title>${escapeXml(title)}</title>`;
    if (alt) svg += `<desc>${escapeXml(alt)}</desc>`;
    svg += inner;
    svg += '</svg>';
    return svg;
}

/**
 * QR artwork with an optional frame and label, ready for preview/export/print.
 */
export function qrArtworkSvg(text, settings = {}, { label = '', logo = '' } = {}) {
    const {
        error_level = 'Q',
        module_style = 'rounded',
        eye_style = 'rounded',
        frame_style = 'none',
        quiet_zone = 4,
        foreground_color = '#000000',
        background_color = '#ffffff',
        eye_color = '#000000',
        frame_label = '',
        logo_ratio = 0.18,
    } = settings;

    const finalLabel = String(frame_label || label || '').trim();
    const { inner, size } = qrSvgInner(text, {
        errorLevel: error_level,
        moduleStyle: module_style,
        eyeStyle: eye_style,
        quietZone: quiet_zone,
        foregroundColor: foreground_color,
        backgroundColor: background_color,
        eyeColor: eye_color,
        logo,
        logoRatio: logo_ratio,
    });

    const font = "system-ui, -apple-system, 'Segoe UI', 'IBM Plex Sans Arabic', sans-serif";

    if (frame_style === 'none') {
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${size}" preserveAspectRatio="xMinYMin meet"><g>${inner}</g></svg>`;
    }

    if (frame_style === 'label') {
        const labelH = finalLabel ? 46 : 0;
        const totalH = size + labelH;
        let out = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${totalH}" preserveAspectRatio="xMinYMin meet">`;
        out += `<rect x="0" y="0" width="${size}" height="${totalH}" fill="${background_color}"/>`;
        out += `<g>${inner}</g>`;
        if (finalLabel) {
            out += `<text x="${size / 2}" y="${size + 30}" text-anchor="middle" font-family="${font}" font-size="18" fill="${foreground_color}">${escapeXml(finalLabel)}</text>`;
        }
        out += '</svg>';
        return out;
    }

    const pad = Math.round(size * 0.06);
    const labelH = finalLabel ? 44 : 0;
    const totalW = size + pad * 2;
    const totalH = size + pad * 2 + labelH;
    const radius = frame_style === 'card' ? 24 : 12;
    const dash = frame_style === 'ticket' ? ' stroke-dasharray="14 10"' : '';

    let out = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${totalW} ${totalH}" preserveAspectRatio="xMinYMin meet">`;
    out += `<rect x="1" y="1" width="${totalW - 2}" height="${totalH - 2}" rx="${radius}" ry="${radius}" fill="${background_color}" stroke="${foreground_color}" stroke-opacity="0.16" stroke-width="2"${dash}/>`;
    out += `<g transform="translate(${pad},${pad})">${inner}</g>`;
    if (finalLabel) {
        out += `<text x="${totalW / 2}" y="${size + pad + 30}" text-anchor="middle" font-family="${font}" font-size="17" font-weight="600" fill="${foreground_color}">${escapeXml(finalLabel)}</text>`;
    }
    out += '</svg>';
    return out;
}

/**
 * Pure, DOM-free RGBA buffer (used by the decoding test-suite). Honours colours,
 * module/eye styles and the quiet zone. Eyes are rendered solid for reliability.
 * @returns {{width:number,height:number,data:Uint8ClampedArray}}
 */
export function qrMatrixRgba(text, options = {}) {
    const {
        errorLevel = 'M',
        moduleStyle = 'square',
        eyeStyle = 'square',
        quietZone = 4,
        foregroundColor = '#000000',
        backgroundColor = '#ffffff',
        eyeColor = '#000000',
        logo = '',
        logoRatio = 0.18,
    } = options;

    const policy = resolveLogoPolicy(!!logo, errorLevel, logoRatio);
    const { count, modules } = buildMatrix(text, policy.errorLevel);
    const cell = 8;
    const margin = quietZone * cell;
    const width = count * cell + margin * 2;
    const height = width;
    const data = new Uint8ClampedArray(width * height * 4);

    const bg = hexToRgb(backgroundColor);
    const fg = hexToRgb(ensureContrast(foregroundColor, backgroundColor, null));
    const eye = hexToRgb(ensureContrast(eyeColor, backgroundColor, '#' + fg.map((c) => c.toString(16).padStart(2, '0')).join('')));

    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const i = (y * width + x) * 4;
            data[i] = bg[0];
            data[i + 1] = bg[1];
            data[i + 2] = bg[2];
            data[i + 3] = 255;
        }
    }

    const fill = (px, py, col) => {
        if (px < 0 || py < 0 || px >= width || py >= height) return;
        const i = (py * width + px) * 4;
        data[i] = col[0];
        data[i + 1] = col[1];
        data[i + 2] = col[2];
        data[i + 3] = 255;
    };

    const inShape = (dx, dy) => {
        const half = cell / 2;
        const nx = dx - (half - 0.5);
        const ny = dy - (half - 0.5);
        switch (moduleStyle) {
            case 'dots':
                return Math.hypot(nx, ny) <= cell * 0.46;
            case 'diamond':
                return Math.abs(nx) + Math.abs(ny) <= cell * 0.82;
            case 'rounded': {
                const r = cell * 0.32;
                const ax = Math.abs(nx);
                const ay = Math.abs(ny);
                const lim = half - r;
                if (ax <= lim || ay <= lim) return true;
                return Math.hypot(ax - lim, ay - lim) <= r;
            }
            default:
                return true;
        }
    };

    for (let r = 0; r < count; r++) {
        for (let c = 0; c < count; c++) {
            if (!modules[r][c]) continue;
            const isF = isFinder(count, r, c);
            const isT = isTiming(count, r, c);
            const col = isF ? eye : fg;
            const ox = margin + c * cell;
            const oy = margin + r * cell;
            for (let dy = 0; dy < cell; dy++) {
                for (let dx = 0; dx < cell; dx++) {
                    if (isF || isT || inShape(dx, dy)) {
                        fill(ox + dx, oy + dy, col);
                    }
                }
            }
        }
    }

    // Logo overlay (solid centred square simulating the embedded logo).
    if (logo) {
        const logoSize = Math.round(width * policy.logoRatio);
        const start = Math.round((width - logoSize) / 2);
        const pad = Math.round(cell * 0.6);
        const bgCol = bg;
        for (let y = start - pad; y < start + logoSize + pad; y++) {
            for (let x = start - pad; x < start + logoSize + pad; x++) {
                fill(x, y, bgCol);
            }
        }
        const logoCol = [120, 120, 120];
        for (let y = start; y < start + logoSize; y++) {
            for (let x = start; x < start + logoSize; x++) {
                fill(x, y, logoCol);
            }
        }
    }

    return { width, height, data };
}

/**
 * Rasterize an SVG string to a PNG data URL.
 */
export async function rasterizeSvgToPng(svg, size = 1024) {
    return new Promise((resolve, reject) => {
        const canvas = document.createElement('canvas');
        const meta = /viewBox="0 0 ([0-9.]+) ([0-9.]+)"/.exec(svg);
        const ratio = meta ? parseFloat(meta[2]) / parseFloat(meta[1]) : 1;
        canvas.width = size;
        canvas.height = Math.round(size * ratio);
        const ctx = canvas.getContext('2d');
        const img = new Image();
        const url = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
        img.onload = () => {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            try {
                resolve(canvas.toDataURL('image/png'));
            } catch (e) {
                reject(e);
            }
        };
        img.onerror = () => reject(new Error('Failed to rasterize SVG'));
        img.src = url;
    });
}

export { buildMatrix, escapeXml, isFinder, isTiming };
