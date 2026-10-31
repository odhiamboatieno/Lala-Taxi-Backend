// Local-only admin directory evidence. Never use production identities or SMTP.
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';

const container = process.env.LALATAXI_ACCEPTANCE_CONTAINER || 'lala-corporate-acceptance-app-1';
assert.match(container, /^lala-corporate-(?:acceptance|admin-qa)-app-1$/);
const env = Object.fromEntries(execFileSync('docker', ['exec', container, 'printenv'], { encoding: 'utf8' })
  .trim().split('\n').map(line => [line.slice(0, line.indexOf('=')), line.slice(line.indexOf('=') + 1)]));
assert.equal(env.APP_ENV, 'staging');
assert.equal(env.APP_URL, 'http://127.0.0.1:8095');
assert.equal(env.DB_DATABASE, 'lala_corporate_acceptance_test');
assert.equal(env.MAIL_HOST, 'mailpit');
assert.ok(env.LALATAXI_ACCEPTANCE_PASSWORD?.length >= 16);
const { chromium } = await import(process.env.LALATAXI_ACCEPTANCE_PLAYWRIGHT_MODULE || 'playwright');
const output = process.env.LALATAXI_ACCEPTANCE_SCREENSHOTS || '/tmp/lala-corporate-admin-browser/screenshots';
await mkdir(output, { recursive: true });
const browser = await chromium.launch({ headless: true, channel: 'chromium' });
let checks = 0;
async function verifyDesign(page) {
  await page.locator('#admin-page-preloader').waitFor({ state: 'detached' });
  await page.evaluate(() => document.fonts.ready);
  const evidence = await page.evaluate(() => ({
    overflow: document.documentElement.scrollWidth > innerWidth,
    untranslated: /corporate\.[a-z_]/.test(document.querySelector('#layout-wrapper').innerText),
    fieldsLabelled: [...document.querySelectorAll('#layout-wrapper input:not([type=hidden]), #layout-wrapper select, #layout-wrapper textarea')]
      .every(el => el.id && document.querySelector(`label[for="${el.id}"]`)),
    squareControls: [...document.querySelectorAll('#layout-wrapper .btn, #layout-wrapper .form-control')]
      .every(el => getComputedStyle(el).borderRadius === '0px'),
  }));
  if (evidence.overflow) {
    await page.screenshot({ path: path.join(output, `overflow-${page.viewportSize().width}.png`), fullPage: true });
  }
  assert.deepEqual(evidence, { overflow: false, untranslated: false, fieldsLabelled: true, squareControls: true });
  checks += 4;
}
try {
  for (const width of [320, 390, 768, 1280, 1440]) {
    const context = await browser.newContext({ viewport: { width, height: 1000 } });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.name));
    await page.goto(env.APP_URL + '/admin/login');
    await page.locator('#username').fill('qa_njeri');
    await page.locator('#password').fill(env.LALATAXI_ACCEPTANCE_PASSWORD);
    await Promise.all([page.waitForURL(/\/admin\/dashboard$/), page.locator('form button[type=submit]').click()]);
    await page.goto(env.APP_URL + '/admin/corporate-accounts');
    await page.getByRole('heading', { name: 'Corporate accounts', exact: true }).first().waitFor();
    await page.locator('svg[data-lucide=building-2]').first().waitFor({ state: 'attached' });
    await verifyDesign(page);
    // The shared shell renders both sidebar and horizontal-menu markup; only the selected layout is visible.
    const activeMenus = page.locator('.pe-slide.active .pe-nav-content').filter({ hasText: /^Corporate accounts$/ });
    assert.equal(await activeMenus.count(), 2);
    assert.ok((await activeMenus.locator('..').evaluateAll(links => links.map(link => link.pathname)))
      .every(url => url === '/admin/corporate-accounts')); checks += 2;
    const employer = page.getByRole('row').filter({ hasText: 'Mwangaza Workspaces Kenya' });
    assert.equal(await employer.count(), 1); checks++;
    assert.equal(await page.getByRole('row').filter({ hasText: 'Tawi Coast Supplies' }).count(), 1); checks++;
    await page.screenshot({ path: path.join(output, `directory-${width}.png`), fullPage: true });
    await page.getByLabel('Search', { exact: true }).fill('not-an-employer');
    await page.getByRole('button', { name: 'Search', exact: true }).click();
    await page.getByRole('heading', { name: 'No corporate accounts found.', exact: true }).waitFor(); checks++;
    await page.getByRole('link', { name: 'Clear', exact: true }).click();
    await employer.getByRole('link', { name: 'Members', exact: true }).click();
    await page.locator('#corporate-members').waitFor();
    assert.equal(new URL(page.url()).hash, '#corporate-members'); checks++;
    await verifyDesign(page);
    if (width === 390 || width === 1280) {
      await page.screenshot({ path: path.join(output, `details-${width}.png`), fullPage: true });
      const invitation = page.locator('#corporate-portal-access details').first();
      assert.equal(await invitation.getAttribute('open'), null); checks++;
      await invitation.locator('summary').click();
      await invitation.locator('input[name=email]').waitFor({ state: 'visible' }); checks++;
      await invitation.locator('summary').click();
    }
    await page.getByRole('link', { name: 'All accounts', exact: true }).click();
    await page.getByRole('link', { name: 'Create account', exact: true }).click();
    await page.getByLabel('Owning operator', { exact: true }).waitFor();
    await verifyDesign(page);
    await page.screenshot({ path: path.join(output, `create-${width}.png`), fullPage: true });
    assert.deepEqual(errors, []); checks++;
    await context.close();
    console.log(`PASS responsive admin pages at ${width}px`);
  }

  // Exercise actual forms once, using only a new fictional employer and an unaffiliated acceptance Rider.
  const context = await browser.newContext({ viewport: { width: 1280, height: 1000 } });
  const page = await context.newPage();
  await page.goto(env.APP_URL + '/admin/login');
  await page.locator('#username').fill('qa_njeri');
  await page.locator('#password').fill(env.LALATAXI_ACCEPTANCE_PASSWORD);
  await Promise.all([page.waitForURL(/\/admin\/dashboard$/), page.locator('form button[type=submit]').click()]);
  await page.goto(env.APP_URL + '/admin/corporate-accounts/create');
  await page.getByRole('button', { name: 'Save account', exact: true }).click();
  assert.equal(await page.locator('form:invalid').count(), 1); checks++;
  assert.equal(await page.getByRole('button', { name: 'Save account', exact: true }).isEnabled(), true); checks++;
  await page.getByLabel('Owning operator', { exact: true }).selectOption({ label: 'Lala QA Platform' });
  const name = 'Wema Teams QA ' + Date.now();
  await page.getByLabel('Account name', { exact: true }).fill(name);
  await page.getByLabel('Legal billing name', { exact: true }).fill('Wema Teams Kenya QA Limited');
  await page.getByLabel('Finance email address', { exact: true }).fill('finance@wema.acceptance.lalataxi.test');
  await page.getByLabel('Billing address', { exact: true }).fill('Fictional QA address, Kisumu, Kenya');
  await page.getByLabel('Billing timezone', { exact: true }).selectOption('Africa/Nairobi');
  await page.getByRole('button', { name: 'Save account', exact: true }).click();
  await page.getByRole('heading', { name, exact: true }).waitFor(); checks++;
  const accountUrl = page.url();
  await page.getByRole('link', { name: 'Edit account', exact: true }).click();
  await page.getByLabel('Finance contact name', { exact: true }).fill('Wanjiku Otieno QA');
  await page.getByRole('button', { name: 'Save account', exact: true }).click();
  await page.getByText('Wanjiku Otieno QA', { exact: true }).waitFor(); checks++;
  const personalRider = await page.locator('#new_member option').evaluateAll(options => options.find(option => option.textContent.includes('Kamau Mutua'))?.value);
  assert.ok(personalRider, 'Only the seeded personal QA Rider is used');
  await page.locator('#new_member').selectOption(personalRider);
  await page.locator('#new_role').selectOption('member');
  await page.getByRole('button', { name: 'Assign member', exact: true }).click();
  await page.locator('#corporate-members tbody').getByText('Kamau Mutua', { exact: false }).waitFor(); checks++;
  await page.locator('#corporate-members').getByRole('button', { name: 'Remove affiliation', exact: true }).click();
  await page.locator('#corporate-members tbody').getByText('No members assigned.', { exact: true }).waitFor(); checks++;
  const archive = page.getByRole('button', { name: 'Archive account', exact: true });
  const writes = [];
  page.on('request', request => { if (request.method() === 'POST') writes.push(request.url()); });
  page.once('dialog', dialog => dialog.dismiss());
  await archive.click();
  await page.waitForTimeout(100);
  assert.deepEqual(writes, []); checks++;
  assert.equal(await archive.isEnabled(), true); checks++;
  page.once('dialog', dialog => dialog.accept());
  await archive.click();
  await page.locator('#layout-wrapper').getByRole('status').filter({ hasText: 'This account is archived.' }).waitFor(); checks++;
  assert.equal(await page.getByRole('button', { name: 'Assign member', exact: true }).count(), 0); checks++;
  assert.equal(page.url(), accountUrl); checks++;
  await page.getByRole('link', { name: 'All accounts', exact: true }).click();
  await page.getByLabel('Status', { exact: true }).selectOption('Archived');
  await page.getByRole('button', { name: 'Search', exact: true }).click();
  await page.getByRole('row').filter({ hasText: name }).waitFor(); checks++;
  await context.close();
  console.log(`PASS ${checks} isolated admin browser checks; screenshots: ${output}`);
} finally {
  await browser.close();
}
