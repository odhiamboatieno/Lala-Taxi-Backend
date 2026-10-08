const { chromium } = require(require.resolve('playwright', { paths: [process.env.PLAYWRIGHT_MODULE_PATH || require('path').resolve(__dirname, '../../prototype')] }));
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const base = process.env.CORPORATE_PREVIEW_URL || 'http://127.0.0.1:8098';
const company = process.env.CORPORATE_PREVIEW_COMPANY || '1';
const account = process.env.CORPORATE_PREVIEW_ACCOUNT || '1';
const prefix = `/admin/companies/${company}/corporate-accounts`;
const output = path.resolve(__dirname, '../design-review/corporate-management');

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true, channel: 'chrome' });
  const results = [];
  try {
    for (const width of [1440, 1024, 390]) {
      for (const theme of ['light', 'dark']) {
        for (const [name, route] of [['accounts', prefix], ['create', `${prefix}/accounts/create`], ['account', `${prefix}/accounts/${account}`], ['edit', `${prefix}/accounts/${account}/edit`]]) {
          const page = await browser.newPage({ viewport: { width, height: 1000 } });
          const errors = [];
          page.on('pageerror', error => errors.push(error.message));
          await page.route('**/*', request => {
            if (/^https:\/\/(code.jquery.com\/jquery-3.6.0.min.js|cdnjs.cloudflare.com\/ajax\/libs\/jquery-autocomplete\/1.0.7\/jquery.auto-complete.min.js)$/.test(request.request().url())) return request.continue();
            if (!request.request().url().startsWith(base)) return request.fulfill({ status: 200, contentType: 'text/plain', body: '' });
            if (request.request().method() !== 'GET') return request.abort();
            return request.continue();
          });
          await page.addInitScript(value => localStorage.setItem('data-bs-theme', value), theme);
          const response = await page.goto(base + route, { waitUntil: 'networkidle' });
          assert.equal(response.status(), 200, `HTTP failure ${route}`);
          await page.evaluate(() => document.fonts.ready);
          await page.screenshot({ path: path.join(output, `${name}-${width}-${theme}.png`), fullPage: true });
          const checks = await page.evaluate(() => {
            const radii = [...document.querySelectorAll('.btn, .card, .form-control')].map(element => getComputedStyle(element).borderRadius);
            const labels = [...document.querySelectorAll('#layout-wrapper input:not([type=hidden]), #layout-wrapper textarea, #layout-wrapper select')].every(element => element.id && document.querySelector(`label[for="${element.id}"]`));
            return { overflow: document.documentElement.scrollWidth > innerWidth, radii: [...new Set(radii)], labels,
              untranslated: /corporate\.[a-z_]/.test(document.querySelector('#layout-wrapper').innerText) };
          });
          results.push({ name, width, theme, ...checks, errors });
          fs.writeFileSync(path.join(output, 'checks.json'), JSON.stringify(results, null, 2));
          assert.equal(checks.overflow, false, `Page overflow ${name} ${width}`);
          assert.deepEqual(checks.radii, ['0px'], 'Management elements must retain square corners');
          assert.equal(checks.labels, true, `Missing accessible field labels ${name}`);
          assert.equal(checks.untranslated, false, `Untranslated label ${name}`);
          assert.deepEqual(errors, [], `Browser errors ${name}`);
          await page.close();
        }
      }
    }
    console.log(`Passed ${results.length} corporate management browser checks.`);
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
