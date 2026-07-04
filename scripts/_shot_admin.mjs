import puppeteer from 'puppeteer-core';

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const BASE = process.argv[2];
const wait = (ms) => new Promise((r) => setTimeout(r, ms));
const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox'] });
const page = await browser.newPage();
await page.setViewport({ width: 1280, height: 1100, deviceScaleFactor: 1 });

const errors = [];
page.on('response', (r) => { if (r.status() >= 500) errors.push(`${r.status()} ${r.url()}`); });

// /__preview-admin loguea al staff y redirige a manage-business
await page.goto(BASE + '/__preview-admin', { waitUntil: 'networkidle0' });
await wait(500);
await page.screenshot({ path: 'C:/tmp/admin-config.png', fullPage: false });

await page.goto(BASE + '/admin/orders', { waitUntil: 'networkidle0' });
await wait(500);
await page.screenshot({ path: 'C:/tmp/admin-orders.png', fullPage: false });

// primer cliente -> editar (para ver la sección Crédito)
await page.goto(BASE + '/admin/customers/1/edit', { waitUntil: 'networkidle0' });
await wait(500);
await page.screenshot({ path: 'C:/tmp/admin-customer.png', fullPage: true });

console.log(errors.length ? 'ERRORES 5xx:\n' + errors.join('\n') : 'sin errores 5xx');
await browser.close();
