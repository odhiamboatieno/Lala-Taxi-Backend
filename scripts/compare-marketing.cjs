const { chromium } = require(require.resolve('playwright', { paths: [require('path').resolve(__dirname, '../../prototype')] }));
const fs = require('node:fs');
const path = require('node:path');
const output = path.resolve(__dirname, '../design-review/website');
const references = process.env.MARKETING_REFERENCES || path.join(output, 'references');
const sourceFiles = {
  home: 'exec-0dfd1c97-6c53-45af-ab80-c1d605c97fb8.png',
  business: 'exec-0dfd1c97-6c53-45af-ab80-c1d605c97fb8.png',
  safety: 'exec-ae225014-39a3-4856-88e3-57df6897ffa3.png',
};
(async () => {
  const browser = await chromium.launch({ headless: true, channel: 'chrome' });
  try {
    const page = await browser.newPage({ viewport: { width: 2048, height: 1536 }, deviceScaleFactor: 1 });
    for (const [name, file] of Object.entries(sourceFiles)) {
      const data = file => 'data:image/png;base64,' + fs.readFileSync(file).toString('base64');
      await page.setContent(`<body style="margin:0;display:flex"><img style="width:1024px;height:1536px" src="${data(path.join(references, file))}"><img style="width:1024px;height:1536px" src="${data(path.join(output, name + '-reference-viewport.png'))}"></body>`);
      await page.locator('img').evaluateAll(images => Promise.all(images.map(image => image.decode())));
      await page.screenshot({ path: path.join(output, name + '-comparison.png') });
      if (name === 'home') await page.screenshot({ path: path.join(output, 'home-hero-comparison.png'), clip: { x: 0, y: 70, width: 2048, height: 776 } });
    }
    console.log('Reference and implementation comparisons saved. Source on left; rendered page on right.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
