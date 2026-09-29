const { chromium } = require(require.resolve('playwright', { paths: [process.env.PLAYWRIGHT_MODULE_PATH || require('path').resolve(__dirname, '../../prototype')] }));
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const output = path.resolve(__dirname, '../design-review/admin');
const base = 'http://127.0.0.1:8097';
(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true, channel: 'chrome' });
  const results = [];
  try {
    for (const width of [1440, 1024, 390]) {
      for (const theme of ['light', 'dark']) {
        for (const route of ['/admin/dashboard', '/admin/add_rider', '/admin/design-controls', '/admin/login']) {
          const page = await browser.newPage({ viewport: { width, height: 1000 } });
          const errors = [];
          page.on('pageerror', e => errors.push(e.message));
          // Do not call maps, analytics, authentication or other external services.
          await page.route('**/*', r => {
            if (/^https:\/\/(code.jquery.com\/jquery-3.6.0.min.js|cdnjs.cloudflare.com\/ajax\/libs\/jquery-autocomplete\/1.0.7\/jquery.auto-complete.min.js)$/.test(r.request().url())) return r.continue();
            if (!r.request().url().startsWith(base)) return r.fulfill({ status: 200, contentType: 'text/plain', body: '' });
            if (r.request().method() !== 'GET') return r.abort();
            return r.continue();
          });
          await page.addInitScript(theme => localStorage.setItem('data-bs-theme', theme), theme);
          const response = await page.goto(base + route, { waitUntil: 'networkidle' });
          if (response.status() !== 200) {
            console.log((await page.locator('body').innerText()).slice(0, 3000));
            throw new Error(`HTTP ${response.status()} ${route}`);
          }
          await page.evaluate(() => document.fonts.ready);
          // Login intentionally defaults to light; exercise its dark CSS explicitly.
          if (route.endsWith('login')) await page.locator('html').evaluate((el, theme) => el.setAttribute('data-bs-theme', theme), theme);
          await page.waitForTimeout(1300);
          const name = route.split('/').pop();
          await page.screenshot({ path: path.join(output, `${name}-${width}-${theme}.png`), fullPage: true });
          const styles = await page.evaluate(() => {
            const style = selector => { const node = document.querySelector(selector); return node ? { radius: getComputedStyle(node).borderRadius, color: getComputedStyle(node).color, background: getComputedStyle(node).backgroundColor } : null; };
            return { overflow: document.documentElement.scrollWidth > innerWidth, button: style('.btn-primary'), input: style('.form-control'), card: style('.card'), avatar: style('.rounded-circle'), font: getComputedStyle(document.body).fontFamily };
          });
          results.push({ width, theme, route, ...styles, errors });
          fs.writeFileSync(path.join(output, 'checks.json'), JSON.stringify(results, null, 2));
          if (styles.button) assert.equal(styles.button.radius, '0px', 'Button must stay square');
          if (styles.input) assert.equal(styles.input.radius, '0px', 'Input must stay square');
          if (styles.card) assert.equal(styles.card.radius, '0px', 'Card must stay square');
          assert.equal(styles.overflow, false, `Viewport overflow ${route} ${width}`);
          assert.deepEqual(errors.filter(error => route.endsWith('add_rider') && error === 'google is not defined' ? false : true), [], `Unexpected browser errors on ${route}`);
          if (route.endsWith('login')) {
            await page.locator('[user="Dispatcher"]').click();
            assert.equal(await page.locator('[name="user_type"]').inputValue(), 'Dispatcher');
            await page.locator('[user="Admin"]').click();
            assert.equal(await page.locator('[name="user_type"]').inputValue(), 'Admin');
          }
          if (route.endsWith('design-controls')) {
            assert.equal(styles.button.background, 'rgb(255, 194, 26)');
            assert.equal(styles.avatar.radius, '50%');
            await page.locator('#preview-table_next a').click();
            assert.match(await page.locator('#preview-table tbody').innerText(), /Mary/);
            await page.locator('#preview-table_previous a').click();
            await page.locator('#preview-table .dropdown-toggle, #preview-table [data-bs-toggle="dropdown"]').first().click();
            assert.equal(await page.locator('#preview-table .dropdown-menu.show').count(), 1);
            await page.keyboard.press('Escape');
            assert.equal(await page.locator('#disabled').isDisabled(), true);
            await page.locator('#filter-name').fill('Mary');
            await page.locator('#apply-filter').click();
            assert.equal(await page.locator('#preview-table tbody tr').count(), 1);
            assert.match(await page.locator('#preview-table tbody').innerText(), /Mary/);
            await page.locator('[data-bs-target="#preview-modal"]').first().click();
            await page.locator('#preview-modal').waitFor({ state: 'visible' });
            await page.waitForTimeout(400);
            await page.keyboard.press('Escape');
            await page.locator('#preview-modal').waitFor({ state: 'hidden' });
            const themeBefore = await page.locator('html').getAttribute('data-bs-theme');
            await page.locator('#toggleMode button:visible').first().click();
            assert.notEqual(await page.locator('html').getAttribute('data-bs-theme'), themeBefore);
            await page.locator('#toggleSidebar').click();
            await page.waitForTimeout(300);
            if (width < 992) assert.equal(await page.locator('#sidebar').evaluate(el => el.classList.contains('show')), true);
          }
          await page.close();
        }
      }
    }
    fs.writeFileSync(path.join(output, 'checks.json'), JSON.stringify(results, null, 2));
    console.log(`Passed ${results.length} renders and interaction checks. Evidence: ${output}`);
    console.log('Known preview limitation: Google Maps is not configured on Add Rider; its existing integration raises google is not defined.');
  } finally { await browser.close(); }
})().catch(e => { console.error(e); process.exitCode = 1; });
