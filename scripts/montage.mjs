// Genera una hoja de contactos con todos los iconos para QA visual.
import sharp from 'sharp';
import { readdirSync } from 'node:fs';
import { join } from 'node:path';

const dir = 'public/images/icons';
const keys = readdirSync(dir).filter((f) => f.endsWith('.webp')).sort();
const cols = 6, cell = 150, icon = 128, pad = (cell - icon) / 2;
const rows = Math.ceil(keys.length / cols);
const W = cols * cell, H = rows * cell;

const comps = [];
for (let i = 0; i < keys.length; i++) {
    const buf = await sharp(join(dir, keys[i]))
        .resize(icon, icon, { fit: 'contain', background: { r: 248, g: 248, b: 248, alpha: 1 } })
        .png()
        .toBuffer();
    comps.push({ input: buf, left: (i % cols) * cell + pad, top: Math.floor(i / cols) * cell + pad });
}

await sharp({ create: { width: W, height: H, channels: 4, background: { r: 248, g: 248, b: 248, alpha: 1 } } })
    .composite(comps)
    .png()
    .toFile('C:/tmp/icons-montage.png');

console.log('montage ok', keys.length, W + 'x' + H);
