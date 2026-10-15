import { qrMatrixRgba } from '../../resources/js/Support/qr-render.js';
import jsQR from 'jsqr';

const cases = [
  { name: 'default', data: 'https://example.com/m/demo-restaurant' },
  { name: 'atelier', data: 'https://example.com/m/demo-restaurant', opts: { moduleStyle: 'square', eyeStyle: 'square', foregroundColor: '#2c2622', backgroundColor: '#f7f1e7', eyeColor: '#b4552d' } },
  { name: 'pulse', data: 'https://example.com/m/demo-restaurant', opts: { moduleStyle: 'dots', eyeStyle: 'circle', foregroundColor: '#14161d', backgroundColor: '#ffffff', eyeColor: '#ff4d1c' } },
  { name: 'mosaic', data: 'https://example.com/m/demo-restaurant', opts: { moduleStyle: 'rounded', eyeStyle: 'rounded', foregroundColor: '#2b2118', backgroundColor: '#f4ead7', eyeColor: '#0f6b5c' } },
  { name: 'neon', data: 'https://example.com/m/demo-restaurant', opts: { moduleStyle: 'diamond', eyeStyle: 'circle', foregroundColor: '#07070c', backgroundColor: '#ffffff', eyeColor: '#ff2d95' } },
];

let failures = 0;
for (const c of cases) {
  const img = qrMatrixRgba(c.data, c.opts || {});
  const res = jsQR(img.data, img.width, img.height);
  if (!res || res.data !== c.data) {
    failures++;
    console.error('FAIL', c.name, res && res.data);
  } else {
    console.log('OK', c.name);
  }
}

if (failures) {
  console.error('QR decode test failed:', failures);
  process.exit(1);
} else {
  console.log('QR decode tests passed:', cases.length);
  process.exit(0);
}
