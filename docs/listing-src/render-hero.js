const { chromium } = require('playwright');
(async () => {
    const b = await chromium.launch();
    const ctx = await b.newContext({ viewport: { width: 1280, height: 720 }, deviceScaleFactor: 2 });
    const p = await ctx.newPage();
    await p.goto('file://' + __dirname + '/listing-hero.html');
    await p.waitForTimeout(500);
    await p.screenshot({ path: 'hero-2560x1440.png' });
    await b.close();
})();
