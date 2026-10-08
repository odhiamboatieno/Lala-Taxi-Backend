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
try {
  for (const width of [390, 1280]) {
    const context = await browser.newContext({ viewport: { width, height: 900 } });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.name));
    await page.goto('http://127.0.0.1:8095/signin');
    await page.getByRole('link', { name: /Manage your corporate travel/ }).click();
    await page.getByRole('heading', { name: 'Your team. Your journeys. One workspace.' }).waitFor();
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); checks++;
    await page.screenshot({ path: path.join(output, `login-${width}.png`), fullPage: true });
    await page.getByLabel('Work email', { exact: true }).fill('amina@acceptance.lalataxi.test');
    await page.getByLabel('Password', { exact: true }).fill(env.LALATAXI_ACCEPTANCE_PASSWORD);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    await page.getByRole('heading', { name: 'Mwangaza Workspaces Kenya' }).waitFor();
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); checks++;
    await page.screenshot({ path: path.join(output, `dashboard-${width}.png`), fullPage: true });
    for (const [label, expected] of [['Members', 'Members'], ['Portal access', 'Portal access'], ['Account trips', 'Account trips'], ['Invoices', 'Invoices']]) {
      await page.getByRole('navigation').getByRole('link', { name: label, exact: true }).click();
      await page.getByRole('heading', { name: expected, exact: true }).waitFor();
      assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)); checks++;
      await page.screenshot({ path: path.join(output, `${label.toLowerCase().replaceAll(' ', '-')}-${width}.png`), fullPage: true });
    }
    assert.deepEqual(errors, []); checks++;
    await page.getByRole('button', { name: 'Sign out' }).click();
    await page.getByRole('button', { name: 'Sign in', exact: true }).waitFor(); checks++;
    await context.close();
  }
  console.log(`PASS ${checks} actual browser/responsive/logout checks; screenshots: ${output}`);
} finally {
  await browser.close();
}
