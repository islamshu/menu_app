/**
 * Small client helpers for exporting and printing the server-rendered QR
 * images. All QR encoding/rendering now happens server-side (endroid/qr-code),
 * so no QR engine ships to the browser anymore.
 */

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

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * Open a print window containing the rendered QR image (URL or data URI).
 */
export function printQrImage(src, { title = '', subtitle = '' } = {}) {
    const win = window.open('', '_blank', 'width=640,height=760');
    if (!win) {
        return;
    }

    win.document.write(
        '<!doctype html><html dir="rtl"><head><meta charset="utf-8">' +
            `<title>${safeTitle}</title>` +
            '<style>' +
            'body{font-family:system-ui,-apple-system,sans-serif;margin:0;padding:48px;display:flex;flex-direction:column;align-items:center;gap:18px;}' +
            'h1{font-size:24px;margin:0;text-align:center;}' +
            'p{margin:0;color:#64748b;font-size:14px;word-break:break-all;text-align:center;}' +
            'img{max-width:90vw;height:auto;}' +
            '</style></head><body>' +
            `<h1>${escapeHtml(String(title))}</h1>` +
            `<img src="${src}" alt="">` +
            `<p>${escapeHtml(String(subtitle))}</p>` +
            '</body></html>',
    );

    win.document.close();
    win.focus();
    setTimeout(() => win.print(), 350);
}
