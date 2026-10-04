import { expect, test, type Locator, type Page } from '@playwright/test';
import { MEMBERS, signInAs } from './support/members';

/** Presses Tab until `target` has focus (fails after `max` presses). */
async function tabTo(page: Page, target: Locator, max = 40): Promise<void> {
    for (let i = 0; i < max; i++) {
        if (await target.evaluate((el) => el === document.activeElement)) return;
        await page.keyboard.press('Tab');
    }
    throw new Error('Could not reach the element with Tab');
}

const focusIsInside = (dialog: Locator) => dialog.evaluate((el) => el.contains(document.activeElement));

test('the menu opens with the keyboard, keeps focus inside and gives it back on Esc', async ({ page }) => {
    await page.goto('/faq');
    const toggle = page.getByRole('button', { name: 'Abrir menu' });
    await tabTo(page, toggle);
    await page.keyboard.press('Enter');

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    for (let i = 0; i < 15; i++) {
        await page.keyboard.press('Tab');
        expect(await focusIsInside(dialog)).toBe(true);
    }

    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(toggle).toBeFocused();
});

test('the cart drawer opens and closes from the keyboard, focus returns to the cart button', async ({ page }) => {
    await page.goto('/loja');
    const cart = page.getByRole('button', { name: /^Abrir carrinho/ });
    await tabTo(page, cart);
    await page.keyboard.press('Enter');

    const drawer = page.getByRole('dialog').filter({ has: page.getByRole('button', { name: 'Fechar carrinho' }) });
    await expect(drawer).toBeVisible();
    expect(await focusIsInside(drawer)).toBe(true);

    await page.keyboard.press('Escape');
    await expect(drawer).toBeHidden();
    await expect(cart).toBeFocused();
});

test('a report photo opens in the lightbox with Enter and closes with Esc, back on the thumbnail', async ({ page }) => {
    await page.goto('/mapa');
    await page.locator('section[aria-labelledby="relatos"] a[href^="/relatos/"]').first().click();
    await page.waitForURL(/\/relatos\/\d+/);

    const thumbnail = page.getByRole('list', { name: 'Fotos do relato' }).getByRole('button').first();
    test.skip((await thumbnail.count()) === 0, 'the newest demo report has no photo');
    await tabTo(page, thumbnail, 80);
    await page.keyboard.press('Enter');

    const lightbox = page.getByRole('dialog');
    await expect(lightbox).toBeVisible();
    expect(await focusIsInside(lightbox)).toBe(true);
    await page.keyboard.press('Escape');
    await expect(lightbox).toBeHidden();
    await expect(thumbnail).toBeFocused();
});

test('the report wizard moves on with the keyboard and focuses each step title', async ({ page }) => {
    await signInAs(page, MEMBERS.author);
    await page.goto('/relatar');
    const firstTitle = await page.getByRole('heading', { level: 1 }).textContent();

    const firstType = page.getByRole('radio').first();
    await tabTo(page, firstType, 60);
    await page.keyboard.press('Space');
    await expect(firstType).toBeChecked();

    const description = page.getByRole('textbox').first();
    await tabTo(page, description, 30);
    await page.keyboard.type('Uma luz verde parada sobre a serra, depois sumiu de uma vez.');

    const next = page.getByRole('button', { name: /Próximo|Continuar|Avançar/ }).first();
    await tabTo(page, next, 60);
    await page.keyboard.press('Enter');

    // Each step is a region named by its h1; the new one takes focus so screen readers hear where they are.
    const title = page.getByRole('heading', { level: 1 });
    await expect(title).not.toHaveText(firstTitle ?? '');
    await expect(title).toBeFocused();
});

test('every focused control shows the beam-green focus ring', async ({ page }) => {
    await page.goto('/');
    let checked = 0;
    for (let i = 0; i < 10; i++) {
        await page.keyboard.press('Tab');
        const ring = await page.evaluate(() => {
            const el = document.activeElement as HTMLElement | null;
            if (!el || el === document.body) return null;
            const style = getComputedStyle(el);
            return parseFloat(style.outlineWidth) > 0 && style.outlineStyle !== 'none'
                ? style.outlineColor
                : style.boxShadow;
        });
        if (ring === null) continue;
        checked++;
        expect(ring, `focus ring on Tab #${i + 1}`).toMatch(/84,\s*201,\s*51/);
    }
    expect(checked).toBeGreaterThan(3);
});
