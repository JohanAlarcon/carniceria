// Regenera los WebP servidos a partir de los SVG fuente (384x384, nítidos).
// Uso: node scripts/render-icons.mjs
import sharp from 'sharp';
import { readdirSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const dir = 'public/images/icons';
const size = 384;
const svgs = readdirSync(dir).filter((f) => f.endsWith('.svg')).sort();

let ok = 0;
for (const f of svgs) {
    const name = f.replace(/\.svg$/, '');
    // density alta = rasterización nítida del SVG antes del resize.
    await sharp(readFileSync(join(dir, f)), { density: 600 })
        .resize(size, size, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
        .webp({ quality: 92 })
        .toFile(join(dir, name + '.webp'));
    ok++;
}
console.log('render ok:', ok, 'iconos ->', size + 'x' + size, 'webp (density 600, q92)');
