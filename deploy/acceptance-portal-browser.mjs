// Local-only browser evidence. Install Playwright outside the production runtime.
import { execFileSync } from 'node:child_process';
import assert from 'node:assert/strict';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const env = Object.fromEntries(execFileSync('docker', ['exec', 'lala-corporate-acceptance-app-1', 'printenv'], { encoding: 'utf8' })
  .trim().split('\n').map(line => [line.slice(0, line.indexOf('=')), line.slice(line.indexOf('=') + 1)]));
assert.equal(env.APP_ENV, 'staging');
assert.equal(env.DB_DATABASE, 'lala_corporate_acceptance_test');
assert.equal(env.MAIL_HOST, 'mailpit');
assert.ok(env.LALATAXI_ACCEPTANCE_PASSWORD?.length >= 16);
const { chromium } = await import(process.env.LALATAXI_ACCEPTANCE_PLAYWRIGHT_MODULE || 'playwright');
const output = process.env.LALATAXI_ACCEPTANCE_SCREENSHOTS || '/tmp/lala-corporate-portal-browser';
await mkdir(output, { recursive: true });
const browser = await chromium.launch({ headless: true, channel: 'chromium' });
let checks = 0;
async function verifyDesign(page) {
  await page.locator('.portal-brand img').waitFor();
  await page.waitForFunction(() => document.querySelector('svg[data-lucide]'));
  await page.evaluate(() => document.fonts.ready);
  const result = await page.evaluate(() => ({
    overflow: document.documentElement.scrollWidth > innerWidth,
    missingIcons: document.querySelectorAll('i[data-lucide]').length,
    loadedImages: [...document.querySelectorAll('img')].every(img => img.complete && img.naturalWidth > 0),
    square: [...document.querySelectorAll('.portal-card, .portal-button, .form-control, .form-select')].every(el => getComputedStyle(el).borderRadius === '0px'),
    labelled: [...document.querySelectorAll('input:not([type=hidden]), select')].every(el => el.id && document.querySelector(`label[for="${el.id}"]`)),
    theme: getComputedStyle(document.documentElement).getPropertyValue('--portal-accent').trim(),
    untranslated: /corporate\.[a-z_]/.test(document.querySelector('#main').innerText),
  }));
  assert.equal(result.overflow, false);
  assert.equal(result.missingIcons, 0, 'Every native Lucide icon must render');
  assert.equal(result.loadedImages, true);
  assert.equal(result.square, true, 'Retain square controls and cards');
  assert.equal(result.labelled, true);
  assert.equal(result.untranslated, false);
  assert.equal(result.theme, '#ffc21a');
  checks += 7;
}
try {
  for (const width of [320, 390, 768, 1280, 1440]) {
    const context = await browser.newContext({ viewport: { width, height: 900 } });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.name));
    await page.goto('http://127.0.0.1:8095/signin');
    await page.getByRole('link', { name: /Manage your corporate travel/ }).click();
    await page.getByRole('heading', { name: 'Your team. Your journeys. One workspace.' }).waitFor();
    await verifyDesign(page);
    await page.getByRole('button', { name: 'Show password', exact: true }).click();
    assert.equal(await page.locator('#password').getAttribute('type'), 'text'); checks++;
    await page.getByRole('button', { name: 'Hide password', exact: true }).click();
    assert.equal(await page.locator('#password').getAttribute('type'), 'password'); checks++;
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); checks++;
    await page.screenshot({ path: path.join(output, `login-${width}.png`), fullPage: true });
    await page.getByLabel('Work email', { exact: true }).fill('amina@acceptance.lalataxi.test');
    await page.getByLabel('Password', { exact: true }).fill(env.LALATAXI_ACCEPTANCE_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.getByRole('heading', { name: 'Mwangaza Workspaces Kenya' }).waitFor();
    await verifyDesign(page);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); checks++;
    await page.screenshot({ path: path.join(output, `dashboard-${width}.png`), fullPage: true });
    for (const [label, expected] of [['Members', 'Members'], ['Portal access', 'Portal access'], ['Account trips', 'Account trips'], ['Invoices', 'Invoices']]) {
      await page.locator('.portal-nav').getByRole('link', { name: label, exact: true }).click();
      await page.getByRole('heading', { name: expected, exact: true }).waitFor();
      await verifyDesign(page);
      assert.equal(await page.locator('.portal-nav [aria-current=page]').innerText(), label); checks++;
      assert.ok(await page.locator('.portal-nav').evaluate(nav => {
        const bounds = nav.getBoundingClientRect();
        const active = nav.querySelector('[aria-current=page]').getBoundingClientRect();
        return active.left >= bounds.left - 1 && active.right <= bounds.right + 1;
      }), 'Selected mobile navigation must stay visible'); checks++;
      if (label === 'Members' && width === 390) {
        const remove = page.getByRole('button', { name: 'Remove affiliation', exact: true }).first();
        const countBefore = await page.locator('#main tbody tr').count();
        const writes = [];
        const recordWrite = request => { if (request.method() === 'POST') writes.push(request.url()); };
        page.on('request', recordWrite);
        page.once('dialog', dialog => dialog.dismiss());
        await remove.click();
        await page.waitForTimeout(100);
        assert.deepEqual(writes, []);
        assert.equal(await remove.isEnabled(), true);
        assert.equal(await page.locator('#main tbody tr').count(), countBefore);
        page.off('request', recordWrite); checks += 3;
      }
      if (label === 'Portal access') {
        const editor = page.locator('details.portal-staff').first();
        assert.equal(await editor.getAttribute('open'), null); checks++;
        await editor.locator('summary').click();
        await editor.locator('input[name=email]').waitFor({ state: 'visible' }); checks++;
        await editor.locator('summary').click();
      }
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); checks++;
      await page.screenshot({ path: path.join(output, `${label.toLowerCase().replaceAll(' ', '-')}-${width}.png`), fullPage: true });
      if (label === 'Account trips' || label === 'Invoices') {
        const detail = page.locator('#main table tbody a').first();
        if (await detail.count()) {
          await detail.click();
          await verifyDesign(page);
          await page.screenshot({ path: path.join(output, `${label === 'Invoices' ? 'invoice' : 'trip'}-${width}.png`), fullPage: true });
          if (label === 'Invoices') {
            const response = await page.request.get(await page.getByRole('link', { name: 'Download PDF', exact: true }).getAttribute('href'));
            assert.equal(response.status(), 200);
            assert.ok(response.headers()['content-type'].includes('application/pdf')); checks += 2;
          }
        }
      }
    }
    assert.deepEqual(errors, []); checks++;
    await page.getByRole('button', { name: 'Sign out' }).click();
    await page.getByRole('button', { name: 'Sign in', exact: true }).waitFor(); checks++;
    await context.close();
  }
  for (const [role, email, navCount] of [['finance', 'otieno', 3], ['viewer', 'kamau.viewer', 2]]) {
    const context = await browser.newContext({ viewport: { width: 390, height: 900 } });
    const page = await context.newPage();
    await page.goto('http://127.0.0.1:8095/corporate/login');
    await page.getByLabel('Work email', { exact: true }).fill(`${email}@acceptance.lalataxi.test`);
    await page.getByLabel('Password', { exact: true }).fill(env.LALATAXI_ACCEPTANCE_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.locator('.portal-nav').waitFor();
    await verifyDesign(page);
    assert.equal(await page.locator('.portal-nav a').count(), navCount); checks++;
    await page.screenshot({ path: path.join(output, `${role}-390.png`), fullPage: true });
    await page.getByRole('button', { name: 'Sign out' }).click();
    await context.close();
  }
  const guest = await browser.newPage({ viewport: { width: 390, height: 900 } });
  for (const [name, route] of [['forgot', '/corporate/forgot-password'], ['reset', '/corporate/reset-password/visual-check-only?email=visual%40example.test']]) {
    await guest.goto('http://127.0.0.1:8095' + route);
    await verifyDesign(guest);
    await guest.screenshot({ path: path.join(output, `${name}-390.png`), fullPage: true });
  }
  await guest.close();
  console.log(`PASS ${checks} actual browser/responsive/logout checks; screenshots: ${output}`);
} finally {
  await browser.close();
}
