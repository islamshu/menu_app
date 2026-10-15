import qrcode from './qrcode.js';
import { buildMatrix, qrArtworkSvg, qrSvg as qrSvgStyled, rasterizeSvgToPng } from './qr-render.js';

function encode(text, errorCorrection = 'M') {
    const qr = qrcode(0, errorCorrection);
    qr.addData(String(text));
    qr.make();
    return qr;
}

export function qrSvg(text, options = {}) {
    if (options && (options.moduleStyle || options.module_style || options.eyeStyle || options.eye_style || options.foregroundColor)) {
        const settings = {
            error_level: options.errorLevel || options.error_level || 'M',
            module_style: options.moduleStyle || options.module_style || 'square',
            eye_style: options.eyeStyle || options.eye_style || 'square',
            quiet_zone: options.quietZone || options.quiet_zone || options.marginModules || 4,
            foreground_color: options.foregroundColor || options.foreground_color || '#000000',
            background_color: options.backgroundColor || options.background_color || '#ffffff',
            eye_color: options.eyeColor || options.eye_color || options.foregroundColor || '#000000',
        };
        return qrSvgStyled(text, settings);
    }
    return encode(text).createSvgTag({
        cellSize: options.cellSize || 8,
        margin: options.margin || 4,
        alt: options.alt || '',
        title: options.title || '',
        scalable: true,
    });
}

export function qrPngDataUrl(text, options = {}) {
    const qr = encode(text);
    const count = qr.getModuleCount();
    const scale = options.scale || 12;
    const margin = options.margin || 4;
    const size = count * scale + margin * 2;

    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;

    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);
    ctx.fillStyle = '#000000';

    for (let row = 0; row < count; row += 1) {
        for (let col = 0; col < count; col += 1) {
            if (qr.isDark(row, col)) {
                ctx.fillRect(margin + col * scale, margin + row * scale, scale, scale);
            }
        }
    }

    return canvas.toDataURL('image/png');
}

export function downloadDataUrl(dataUrl, filename) {
    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

export function downloadTextFile(content, filename, mime = 'text/plain') {
    const blob = new Blob([content], { type: `${mime};charset=utf-8` });
    const url = URL.createObjectURL(blob);
    downloadDataUrl(url, filename);
    URL.revokeObjectURL(url);
}

export function printQrCode(svg, { title = '', subtitle = '' } = {}) {
    const win = window.open('', '_blank', 'width=640,height=760');
    if (!win) {
        return;
    }

    const safeTitle = String(title);
    const safeSubtitle = String(subtitle);

    win.document.write(
        '<!doctype html><html dir="rtl"><head><meta charset="utf-8">' +
            `<title>${safeTitle}</title>` +
            '<style>' +
            'body{font-family:system-ui,-apple-system,sans-serif;margin:0;padding:48px;display:flex;flex-direction:column;align-items:center;gap:18px;}' +
            'h1{font-size:24px;margin:0;text-align:center;}' +
            'p{margin:0;color:#64748b;font-size:14px;word-break:break-all;text-align:center;}' +
            '.qr{max-width:90vw;display:flex;justify-content:center;}' +
            '.qr svg{max-width:100%;height:auto;}' +
            '</style></head><body>' +
            `<h1>${safeTitle}</h1>` +
            `<div class="qr">${svg}</div>` +
            `<p>${safeSubtitle}</p>` +
            '</body></html>',
    );

    win.document.close();
    win.focus();
    setTimeout(() => win.print(), 350);
}

export { buildMatrix, qrArtworkSvg, rasterizeSvgToPng, qrSvgStyled };
export { qrMatrixRgba } from './qr-render.js';
export { encodeQr } from './qr-core.js';
