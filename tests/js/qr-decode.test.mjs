import { qrMatrixRgba, qrSvg } from '../../resources/js/Support/qr-render.js';
import jsQR from 'jsqr';

const data = 'https://example.com/m/demo-restaurant';
const logoData = 'https://example.com/m/logo-check';

// Mirrors config/qr.php so the shipped presets are guaranteed to decode.
const presets = [
  { name: 'noir-luxe', opts: { moduleStyle: 'rounded', eyeStyle: 'rounded', foregroundColor: '#0c0c0f', backgroundColor: '#f4f1ea', eyeColor: '#6b5016' } },
  { name: 'atelier', opts: { moduleStyle: 'square', eyeStyle: 'square', foregroundColor: '#2c2622', backgroundColor: '#f7f1e7', eyeColor: '#b4552d' } },
  { name: 'pulse', opts: { moduleStyle: 'dots', eyeStyle: 'circle', foregroundColor: '#14161d', backgroundColor: '#ffffff', eyeColor: '#e03a10' } },
  { name: 'mosaic', opts: { moduleStyle: 'rounded', eyeStyle: 'rounded', foregroundColor: '#2b2118', backgroundColor: '#f4ead7', eyeColor: '#0f6b5c' } },
  { name: 'neon', opts: { moduleStyle: 'diamond', eyeStyle: 'circle', foregroundColor: '#07070c', backgroundColor: '#ffffff', eyeColor: '#d81b74' } },
];

const cases = [{ name: 'default', data }];

for (const preset of presets) {
  cases.push({ name: preset.name, data, opts: preset.opts });
  // Each preset must also stay readable with a centre logo at its max ratio.
  cases.push({
    name: `${preset.name}+logo`,
    data: logoData,
    opts: { ...preset.opts, errorLevel: 'L', logo: '1', logoRatio: 0.15 },
  });
}

cases.push(
  { name: 'low-contrast-eye-autocorrected', data, opts: { moduleStyle: 'rounded', eyeStyle: 'rounded', foregroundColor: '#0c0c0f', backgroundColor: '#f4f1ea', eyeColor: '#c8a349' } },
  { name: 'logo-low-level', data: logoData, opts: { errorLevel: 'L', logo: '1', logoRatio: 0.15, moduleStyle: 'rounded', eyeStyle: 'rounded', eyeColor: '#c8a349' } },
  { name: 'logo-max-ratio', data: logoData, opts: { errorLevel: 'L', logo: '1', logoRatio: 0.5, moduleStyle: 'dots', eyeStyle: 'circle', eyeColor: '#e03a10' } },
);

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

// Regression guard: styling options must actually change the rendered SVG.
const base = { quietZone: 4, errorLevel: 'Q' };
const a = qrSvg('https://example.com/m/x', { ...base, moduleStyle: 'square', eyeStyle: 'square', eyeColor: '#000000' });
const b = qrSvg('https://example.com/m/x', { ...base, moduleStyle: 'rounded', eyeStyle: 'circle', eyeColor: '#ff0000' });
if (a === b) {
  failures++;
  console.error('FAIL styling-variance: styled SVG identical to default');
} else if (!b.includes('#ff0000')) {
  failures++;
  console.error('FAIL eye-color: eye colour missing from styled SVG');
} else {
  console.log('OK styling-variance');
}

// Regression guard: distinct module styles must produce distinct SVG geometry.
const shapes = ['square', 'rounded', 'dots', 'diamond'].map((moduleStyle) =>
  qrSvg('https://example.com/m/x', { ...base, moduleStyle }),
);
if (new Set(shapes).size !== shapes.length) {
  failures++;
  console.error('FAIL module-styles: styles produced identical output');
} else {
  console.log('OK module-styles');
}

if (failures) {
  console.error('QR decode test failed:', failures);
  process.exit(1);
} else {
  console.log('QR decode tests passed:', cases.length + 2);
  process.exit(0);
}
