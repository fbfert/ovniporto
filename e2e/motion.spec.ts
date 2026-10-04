import { expect, test } from '@playwright/test';

/** Animations that never end (marquee, twinkles, pulses) running on the page right now. */
const endless = () =>
    document.getAnimations().filter((a) => a.playState === 'running' && a.effect?.getTiming().iterations === Infinity)
        .length;

test.describe('with reduced motion', () => {
    test.use({ reducedMotion: 'reduce' });

    test('every endless animation stands still and the strip shows as a static line', async ({ page }) => {
        await page.goto('/');
        await page.waitForLoadState('networkidle');

        expect(await page.evaluate(endless)).toBe(0);
        await expect(page.getByRole('marquee').first()).toBeVisible();
    });

    test('scrolling moves no parallax layer', async ({ page }) => {
        await page.goto('/');
        const layer = page.locator('[data-parallax]').first();
        test.skip((await layer.count()) === 0, 'no parallax layer marked on the home');
        const before = await layer.evaluate((el) => getComputedStyle(el).transform);
        await page.mouse.wheel(0, 900);
        await page.waitForTimeout(300); // let one scroll-linked frame run, if any
        expect(await layer.evaluate((el) => getComputedStyle(el).transform)).toBe(before);
    });
});

test.describe('with motion allowed', () => {
    test.use({ reducedMotion: 'no-preference' });

    test('the marquee runs, and its pause button stops it', async ({ page }) => {
        await page.goto('/');
        const marquee = page.getByRole('marquee').first();
        await marquee.scrollIntoViewIfNeeded();
        expect(await page.evaluate(endless)).toBeGreaterThan(0);

        await marquee.getByRole('button', { name: 'Pausar faixa' }).click();
        await expect(marquee.getByRole('button', { name: 'Pausar faixa' })).toHaveAttribute('aria-pressed', 'true');
        const marqueeRunning = await page.evaluate(() =>
            document
                .getAnimations()
                .some(
                    (a) =>
                        a.playState === 'running' &&
                        'animationName' in a &&
                        String(a.animationName).includes('marquee'),
                ),
        );
        expect(marqueeRunning).toBe(false);
    });
});

test('the Livro lists, under the map, every report the map shows', async ({ page }) => {
    test.setTimeout(180_000);
    await page.goto('/mapa');
    const counter = await page
        .getByText(/^\d+ relatos?/)
        .first()
        .textContent();
    const total = Number(counter?.match(/\d+/)?.[0] ?? NaN);

    const loadMore = page.getByRole('button', { name: 'Carregar mais' });
    // One page at a time: wait for each load to finish before asking for the next.
    while (await loadMore.isVisible()) {
        await loadMore.click();
        await expect(page.locator('button[aria-busy="true"]')).toHaveCount(0, { timeout: 30_000 });
    }
    const listed = await page.locator('section[aria-labelledby="relatos"] a[href^="/relatos/"]').count();
    expect(listed).toBe(total);
});
