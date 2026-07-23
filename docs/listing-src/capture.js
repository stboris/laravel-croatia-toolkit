const { chromium } = require('playwright');

(async () => {
    const b = await chromium.launch();
    const ctx = await b.newContext({ viewport: { width: 1200, height: 900 }, deviceScaleFactor: 2, ignoreHTTPSErrors: true });
    const p = await ctx.newPage();
    await p.goto('https://laravel-croatia-toolkit-sandbox.ddev.site/admin/login');
    await p.locator('input[type=email]').fill('boris@example.test');
    await p.locator('input[type=password]').fill('password');
    await p.getByRole('button', { name: 'Prijavi se' }).click();
    await p.waitForURL((u) => !u.pathname.includes('login'));

    await p.goto('https://laravel-croatia-toolkit-sandbox.ddev.site/admin/demo-companies/create');
    await p.waitForTimeout(800);

    // Real, live lookup against Croatia's Sudski registar (test env) -- OIB for
    // Zagrebačka banka d.d., the toolkit's own documented test fixture (public
    // company registry data, not personal information).
    const oibInput = p.locator('input[id="form.oib"]');
    await oibInput.fill('92963223473');
    await oibInput.blur();
    await p.waitForTimeout(2000);

    await p.locator('.fi-page-content').first().screenshot({ path: 'form-card.png' });

    await b.close();
})();
