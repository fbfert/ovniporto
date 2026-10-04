import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { MEMBERS, signInAs } from './support/members';

const PAGES = ['/', '/mapa', '/loja', '/o-lugar', '/relatar'];

for (const path of PAGES) {
    test(`axe-core finds no critical violation on ${path}`, async ({ page }) => {
        if (path === '/relatar') await signInAs(page, MEMBERS.author);
        await page.goto(path);
        await page.waitForLoadState('networkidle');

        const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
        const describe = (impact: string) =>
            results.violations
                .filter((v) => v.impact === impact)
                .map((v) => `${v.id}: ${v.nodes.map((n) => n.target.join(' ')).join(' | ')}`);
        test.info().annotations.push({ type: 'axe serious', description: describe('serious').join('\n') || 'none' });

        expect(describe('critical')).toEqual([]);
    });
}

test.describe('visual regression of the home', () => {
    test.use({ reducedMotion: 'reduce' });

    for (const width of [390, 1440]) {
        test(`the home looks the same at ${width} px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto('/');
            await page.waitForLoadState('networkidle');
            await page.evaluate(() => document.fonts.ready);

            await expect(page).toHaveScreenshot(`home-${width}.png`, {
                fullPage: true,
                // What changes on its own: random stars, demo dates and counters, report photos.
                mask: [page.locator('canvas'), page.locator('[data-dynamic]'), page.locator('time')],
            });
        });
    }
});
