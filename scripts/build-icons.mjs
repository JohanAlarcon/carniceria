// Convierte los SVG generados por el workflow a .webp livianos (fondo transparente).
// Uso: node scripts/build-icons.mjs "<ruta-al-archivo-json-del-workflow>"
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';
import sharp from 'sharp';

const src = process.argv[2];
if (!src) {
  console.error('Falta la ruta del archivo de resultado del workflow.');
  process.exit(1);
}

const outDir = join('public', 'images', 'icons');
mkdirSync(outDir, { recursive: true });

function unescapeHtml(s) {
  return String(s)
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&#0?39;/g, "'")
    .replace(/&#0?34;/g, '"')
    .replace(/&apos;/g, "'")
    .replace(/&amp;/g, '&');
}

function tryParse(t) {
  try { return JSON.parse(t); } catch { return null; }
}

const raw = readFileSync(src, 'utf8').trim();
const data = tryParse(raw) ?? tryParse(unescapeHtml(raw));
let icons = null;
if (Array.isArray(data)) icons = data;
else if (data && Array.isArray(data.result)) icons = data.result;
if (!Array.isArray(icons)) {
  console.error('No se pudo parsear el JSON de iconos.');
  process.exit(1);
}

let ok = 0, fail = 0;
const failed = [];
for (const it of icons) {
  const key = it.key;
  let svg = unescapeHtml(it.svg).trim();
  if (!svg.startsWith('<svg')) { failed.push(key); fail++; continue; }
  try {
    writeFileSync(join(outDir, key + '.svg'), svg, 'utf8');
    await sharp(Buffer.from(svg), { density: 384 })
      .resize(256, 256, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
      .webp({ quality: 90 })
      .toFile(join(outDir, key + '.webp'));
    ok++;
  } catch (e) {
    console.error('FAIL', key, e.message);
    failed.push(key);
    fail++;
  }
}
console.log(`icons ok=${ok} fail=${fail} total=${icons.length}`);
if (failed.length) console.log('fallidos: ' + failed.join(', '));
