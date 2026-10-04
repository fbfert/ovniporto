import { expect, test } from '@playwright/test';

/**
 * Runs as its own project, before every other spec (playwright.config.ts): the flows approve
 * reports and change counters, and the home must be captured on the freshly seeded database.
 */
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
