import { buildMatrix, escapeXml, isFinder, isTiming } from './qr-core.js';


/**
 * Convert hex to RGB array
 */
function hexToRgb(hex) {
    if (typeof hex !== 'string') return [0, 0, 0];
    let v = hex.trim().replace('#', '');
    if (v.length === 3) {
        v = v.split('').map((x) => x + x).join('');
    }
    if (!/^[0-9a-fA-F]{6}$/.test(v)) return [0, 0, 0];
    return [parseInt(v.slice(0, 2), 16), parseInt(v.slice(2, 4), 16), parseInt(v.slice(4, 6), 16)];
}

function rgbToCss(r, g, b) {
    return `rgb(${Math.round(r)}, ${Math.round(g)}, ${Math.round(b)})`;
}

/**
 * Build RGBA ImageData-like object (for tests/decoding). Pure, DOM-free.
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
    } = options;

    const { count, modules } = buildMatrix(text, errorLevel);
    const cell = 8;
    const margin = quietZone * cell;
    const width = count * cell + margin * 2;
    const height = width;
    const data = new Uint8ClampedArray(width * height * 4);

    const bg = hexToRgb(backgroundColor);
    const fg = hexToRgb(foregroundColor);
    const eye = hexToRgb(eyeColor);

    function setPixel(x, y, r, g, b, a = 255) {
        if (x < 0 || y < 0 || x >= width || y >= height) return;
        const i = (y * width + x) * 4;
        data[i] = r;
        data[i + 1] = g;
        data[i + 2] = b;
        data[i + 3] = a;
    }

    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            setPixel(x, y, bg[0], bg[1], bg[2]);
        }
    }

    for (let r = 0; r < count; r++) {
        for (let c = 0; c < count; c++) {
            if (!modules[r][c]) continue;
            const ox = margin + c * cell;
            const oy = margin + r * cell;
            const isF = isFinder(count, r, c);
            const col = isF ? eye : fg;
            for (let dy = 0; dy < cell; dy++) {
                for (let dx = 0; dx < cell; dx++) {
                    setPixel(ox + dx, oy + dy, col[0], col[1], col[2]);
                }
            }
        }
    }

    return { width, height, data };
}

export { buildMatrix, escapeXml, isFinder, isTiming };

/**
 * Render QR-only SVG (no frame).
 */
export function qrSvg(text, options = {}) {
    const {
        errorLevel = 'M',
        moduleStyle = 'square',
        eyeStyle = 'square',
        quietZone = 4,
        cellSize = 8,
        marginModules,
        foregroundColor = '#000000',
        backgroundColor = '#ffffff',
        eyeColor = '#000000',
        alt = '',
        title = '',
        scalable = true,
    } = options;

    const marginM = marginModules ?? quietZone;
    const { count, modules } = buildMatrix(text, errorLevel);
    const size = count * cellSize + marginM * 2 * cellSize;
    const m = marginM * cellSize;

    const bg = backgroundColor;
    const fg = foregroundColor;
    const eye = eyeColor;

    let path = '';
    for (let r = 0; r < count; r++) {
        for (let c = 0; c < count; c++) {
            if (!modules[r][c]) continue;
            const isF = isFinder(count, r, c);
            const mc = m + c * cellSize;
            const mr = m + r * cellSize;
            const col = isF ? eye : fg;
            const rect = 'l' + cellSize + ',0 0,' + cellSize + ' -' + cellSize + ',0 z';
            path += (path ? ' ' : '') + 'M' + mc + ',' + mr + rect;
        }
    }

    let svg = '';
    svg += '<svg version="1.1" xmlns="http://www.w3.org/2000/svg"';
    svg += !scalable ? ' width="' + size + 'px" height="' + size + 'px"' : '';
    svg += ' viewBox="0 0 ' + size + ' ' + size + '" ';
    svg += ' preserveAspectRatio="xMinYMin meet"';
    if (title || alt) {
        const id = 'qr-desc-' + Math.random().toString(36).slice(2, 8);
        svg += ' role="img" aria-labelledby="' + id + '"';
    }
    svg += '>';
    if (title) svg += '<title id="qr-title-' + Math.random().toString(36).slice(2, 8) + '">' + escapeXml(title) + '</title>';
    if (alt) svg += '<desc>' + escapeXml(alt) + '</desc>';
    svg += '<rect width="100%" height="100%" fill="' + bg + '" cx="0" cy="0"/>';
    svg += '<path d="' + path + '" stroke="transparent" fill="' + fg + '"/>';
    svg += '</svg>';
    return svg;
}

/**
 * QR artwork with frame + label (SVG).
 */
export function qrArtworkSvg(text, settings = {}, { label = '' } = {}) {
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
    } = settings;

    const finalLabel = frame_label || label || '';
    const q = qrSvg(text, {
        errorLevel: error_level,
        moduleStyle: module_style,
        eyeStyle: eye_style,
        quietZone: quiet_zone,
        foregroundColor: foreground_color,
        backgroundColor: background_color,
        eyeColor: eye_color,
        scalable: true,
    });

    if (frame_style === 'none' || frame_style === 'label') {
        // return QR with optional label space handled? simple: wrap in container; for 'label' append text below
        // extract inner SVG viewBox/size
        const m = /viewBox="0 0 ([0-9.]+) ([0-9.]+)"/.exec(q);
        const w = m ? parseFloat(m[1]) : 256;
        const h = m ? parseFloat(m[2]) : 256;
        const labelH = finalLabel ? 40 : 0;
        const totalH = h + labelH;
        const svgInner = q.replace(/<svg[^>]*>/, '').replace('</svg>', '');
        let out = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + w + ' ' + totalH + '" preserveAspectRatio="xMinYMin meet">';
        out += '<rect width="100%" height="100%" fill="' + background_color + '"/>';
        // re-insert inner (QR) at top
        out += svgInner.replace(/<rect[^>]*fill="[^"]*"[^>]*\/>/, '');
        if (finalLabel) {
            out += '<text x="' + w / 2 + '" y="' + (h + 28) + '" text-anchor="middle" font-family="system-ui,-apple-system,Segoe UI,sans-serif" font-size="16" fill="' + foreground_color + '">' + escapeXml(finalLabel) + '</text>';
        }
        out += '</svg>';
        return out;
    }

    // card/ticket: add rounded border
    const m = /viewBox="0 0 ([0-9.]+) ([0-9.]+)"/.exec(q);
    const w = m ? parseFloat(m[1]) : 256;
    const h = m ? parseFloat(m[2]) : 256;
    const pad = 20;
    const labelH = finalLabel ? 40 : 0;
    const totalW = w + pad * 2;
    const totalH = h + pad * 2 + labelH;
    const svgInner = q.replace(/<svg[^>]*>/, '').replace('</svg>', '');
    let out = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + totalW + ' ' + totalH + '" preserveAspectRatio="xMinYMin meet">';
    out += '<rect width="100%" height="100%" rx="20" ry="20" fill="' + background_color + '" stroke="#e5e7eb" stroke-width="1"/>';
    out += '<g transform="translate(' + pad + ',' + pad + ')">' + svgInner.replace(/<rect[^>]*fill="[^"]*"[^>]*\/>/, '') + '</g>';
    if (finalLabel) {
        out += '<text x="' + totalW / 2 + '" y="' + (h + pad * 2 + 24) + '" text-anchor="middle" font-family="system-ui,-apple-system,Segoe UI,sans-serif" font-size="15" fill="' + foreground_color + '">' + escapeXml(finalLabel) + '</text>';
    }
    out += '</svg>';
    return out;
}

/**
 * Rasterize SVG string to PNG Data URL (returns Promise<string>).
 */
export async function rasterizeSvgToPng(svg, size = 1024) {
    return new Promise((resolve, reject) => {
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.onload = () => {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);
            ctx.drawImage(img, 0, 0, size, size);
            try {
                resolve(canvas.toDataURL('image/png'));
            } catch (e) {
                reject(e);
            }
        };
        img.onerror = reject;
        const svgBlob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(svgBlob);
        img.src = url;
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
}
