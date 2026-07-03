import sharp from 'sharp';
const SCRATCH = 'C:/Users/HP/AppData/Local/Temp/claude/c--laragon-www-carniceria/7c216ab8-a8d4-498e-9418-ec6f4f986305/scratchpad';
const rows = [
  [ { file: 'public/images/icons/beef.svg' }, { file: SCRATCH + '/beef_refined.svg' } ],
  [ { file: 'public/images/icons/pork.svg' }, { file: SCRATCH + '/pork_refined.svg' } ],
];
const cell = 240, icon = 168, pad = (cell - icon) / 2, headH = 34;
const cols = 2;
const W = cols * cell, H = headH + rows.length * cell;
const comps = [];
// headers
const header = Buffer.from(`<svg width="${W}" height="${headH}"><rect width="${W}" height="${headH}" fill="#ffffff"/>
  <text x="${cell/2}" y="23" font-family="Arial" font-size="16" font-weight="700" fill="#555" text-anchor="middle">ACTUAL</text>
  <text x="${cell + cell/2}" y="23" font-family="Arial" font-size="16" font-weight="700" fill="#b91c1c" text-anchor="middle">REFINADO</text></svg>`);
comps.push({ input: await sharp(header).png().toBuffer(), left: 0, top: 0 });
for (let r = 0; r < rows.length; r++) {
  for (let c = 0; c < cols; c++) {
    const card = Buffer.from(`<svg width="${cell}" height="${cell}"><rect x="8" y="8" width="${cell-16}" height="${cell-16}" rx="22" fill="#fbeceb"/></svg>`);
    comps.push({ input: await sharp(card).png().toBuffer(), left: c * cell, top: headH + r * cell });
    const buf = await sharp(rows[r][c].file).resize(icon, icon, { fit: 'contain', background: { r:0,g:0,b:0,alpha:0 } }).png().toBuffer();
    comps.push({ input: buf, left: c * cell + pad, top: headH + r * cell + pad });
  }
}
await sharp({ create: { width: W, height: H, channels: 4, background: { r:255,g:255,b:255,alpha:1 } } })
  .composite(comps).png().toFile('C:/tmp/icon-compare.png');
console.log('ok', W + 'x' + H);
