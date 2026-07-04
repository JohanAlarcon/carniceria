import puppeteer from 'puppeteer-core';

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const BASE = process.argv[2] || 'http://127.0.0.1:8949';
const wait = (ms) => new Promise((r) => setTimeout(r, ms));

const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox'] });
const page = await browser.newPage();
await page.setViewport({ width: 1280, height: 1000, deviceScaleFactor: 1 });

await page.goto(BASE + '/login', { waitUntil: 'networkidle0' });
await page.type('#email', 'cliente@demo.test');
await page.type('#password', 'password');
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button[type=submit]')]);

await page.evaluate(() => {
    localStorage.setItem('carniceria_cart_v1', JSON.stringify([
        { variant_id: 1, quantity: 12, unit_price: 5.31, name_es: 'Diezmillo', name_en: 'Chuck Roll', unit_label_es: 'lb', unit_label_en: 'lb' },
    ]));
});

await page.goto(BASE + '/checkout', { waitUntil: 'networkidle0' });
await wait(400);
await page.screenshot({ path: 'C:/tmp/checkout-d.png', fullPage: true });

await page.evaluate(() => {
    const b = [...document.querySelectorAll('button')].find((x) => /Crédito/.test(x.textContent));
    b && b.click();
});
await wait(300);
await page.screenshot({ path: 'C:/tmp/checkout-d-credit.png', fullPage: true });

await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2 });
await page.goto(BASE + '/checkout', { waitUntil: 'networkidle0' });
await wait(400);
await page.screenshot({ path: 'C:/tmp/checkout-m.png', fullPage: true });

console.log('shots ok');
await browser.close();
