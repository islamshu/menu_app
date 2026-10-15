import qrcode from './qrcode.js';

/**
 * Encode text into a QR matrix using the existing encoder.
 * @param {string} text
 * @param {string} errorCorrection 'L'|'M'|'Q'|'H'
 */
export function encodeQr(text, errorCorrection = 'M') {
    const qr = qrcode(0, errorCorrection);
    qr.addData(String(text));
    qr.make();
    return qr;
}

/**
 * Build module metadata from matrix.
 * Finder is standard 7x7 at three corners. This is a geometric inference.
 */
export function buildMatrix(text, errorCorrection = 'M') {
    const qr = encodeQr(text, errorCorrection);
    const count = qr.getModuleCount();
    const modules = Array(count).fill().map(() => Array(count).fill(false));
    for (let r = 0; r < count; r++) {
        for (let c = 0; c < count; c++) {
            modules[r][c] = qr.isDark(r, c);
        }
    }
    return {
        qr,
        count,
        modules,
    };
}

export function isFinder(count, r, c) {
    if (r < 7 && c < 7) return true; // top-left
    if (r < 7 && c >= count - 7) return true; // top-right
    if (r >= count - 7 && c < 7) return true; // bottom-left
    return false;
}

export function isTiming(count, r, c) {
    if (r === 6 && c >= 7 && c <= count - 8) return true;
    if (c === 6 && r >= 7 && r <= count - 8) return true;
    return false;
}

export function isAlignment(count, r, c) {
    // standard positions for common versions are near center; we can detect by pattern
    // but matrix already has it. Treat non-finder/timing darks in inner area as alignment? simpler: skip special
    return false;
}

export function escapeXml(s) {
    return String(s).replace(/[<>&'"]/g, (ch) => {
        switch (ch) {
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '&': return '&amp;';
            case '"': return '&quot;';
            case "'": return '&apos;';
            default: return ch;
        }
    });
}
