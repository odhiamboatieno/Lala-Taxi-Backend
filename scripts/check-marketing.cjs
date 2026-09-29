const { chromium } = require(require.resolve('playwright', { paths: [process.env.PLAYWRIGHT_MODULE_PATH || require('path').resolve(__dirname, '../../prototype')] }));
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const output = path.resolve(__dirname, '../design-review/website');
const base = process.env.MARKETING_PREVIEW_URL || 'http://127.0.0.1:8096';
(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true, channel: 'chrome' });
  const errors = [];
  const results = [];
  try {
    for (const width of [1440, 1024, 768, 390, 320]) {
      const page = await browser.newPage({ viewport: { width, height: width >= 1024 ? 1536 : 844 }, deviceScaleFactor: 1 });
      page.on('pageerror', error => errors.push(error.message));
      page.on('console', message => { if (message.type() === 'error') errors.push(message.text()); });
      for (const route of ['/', '/business', '/business/fleets', '/safety', '/ride', '/drive', '/requirements', '/driver_app', '/drive_safety', '/signin', '/signup', '/signin_company']) {
        const response = await page.goto(base + route, { waitUntil: 'networkidle' });
        assert.equal(response.status(), 200, route);
        await page.evaluate(() => document.fonts.ready);
        await page.locator('footer').scrollIntoViewIfNeeded();
        await page.waitForTimeout(150);
        await page.evaluate(() => window.scrollTo(0, 0));
        const check = await page.evaluate(() => ({
          overflow: document.documentElement.scrollWidth > innerWidth,
          brokenImages: [...document.images].filter(image => !image.complete || !image.naturalWidth).map(image => image.getAttribute('src')),
          missingIcons: document.querySelectorAll('i[data-lucide]').length,
          heading: document.querySelector('h1')?.textContent,
          unresolvedTranslations: /website\.[a-z_]+/.test(document.body.innerText),
        }));
        assert.equal(check.overflow, false, `Overflow ${width} ${route}`);
        assert.deepEqual(check.brokenImages, [], `Images ${width} ${route}`);
        assert.equal(check.unresolvedTranslations, false, `Translations ${width} ${route}`);
        assert.equal(check.missingIcons, 0, `Icons ${width} ${route}`);
        const name = route === '/' ? 'home' : route.slice(1).replaceAll('/', '-');
        await page.screenshot({ path: path.join(output, `${name}-${width}.png`), fullPage: true });
        if (width === 1024) await page.screenshot({ path: path.join(output, `${name}-reference-viewport.png`) });
        results.push({ width, route, ...check });
      }
      await page.goto(base, { waitUntil: 'networkidle' });
      await page.locator('[data-app-open]').first().click();
      assert.equal(await page.locator('#app-download').evaluate(el => el.open), true);
      await page.keyboard.press('Escape');
      assert.equal(await page.locator('#app-download').evaluate(el => el.open), false);
      assert.equal(await page.locator('[data-app-open]').first().evaluate(el => el === document.activeElement), true);
      if (width <= 850) {
        await page.locator('[data-menu-toggle]').click();
        assert.equal(await page.locator('[data-menu-toggle]').getAttribute('aria-expanded'), 'true');
        await page.locator('#mobile-navigation a[href$="/business"]').click();
        assert.equal(new URL(page.url()).pathname, '/business');
        await page.locator('[data-menu-toggle]').click();
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('[data-menu-toggle]').getAttribute('aria-expanded'), 'false');
      }
      await page.goto(base + '/safety', { waitUntil: 'networkidle' });
      await page.locator('summary').first().click();
      assert.equal(await page.locator('details').first().getAttribute('open'), '');
      await page.close();
    }
    assert.deepEqual(errors, []);
    fs.writeFileSync(path.join(output, 'checks.json'), JSON.stringify({ results, errors }, null, 2));
    console.log(`Passed ${results.length} responsive page checks, navigation, download dialog and safety accordion. No browser errors.`);
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
