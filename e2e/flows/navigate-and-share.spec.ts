import { test } from '@playwright/test';
import { expect, FLOW_TIMEOUT } from '../support/expect';
import { t } from '../../resources/js/i18n/pt-BR';

const copy = t.sightingPage;

test.use({ permissions: ['clipboard-read', 'clipboard-write'] });
test.describe.configure({ timeout: FLOW_TIMEOUT });

test('a visitor opens an approved report from the Livro and shares it', async ({ page, baseURL }) => {
    await test.step('the Livro de avistamentos lists the approved reports', async () => {
        await page.goto('/mapa');
        await expect(page.getByRole('heading', { level: 1, name: t.logbookPage.title })).toBeVisible();
        // The section and the Leaflet container share the label; the first one is the section.
        await expect(page.getByRole('region', { name: t.logbookPage.mapLabel }).first()).toBeVisible();
    });

    let reportUrl = '';
    await test.step('a report card opens the report page with its details', async () => {
        // The polaroid links get no accessible name of their own (the <figure> inside does not lend one),
        // so the card is found through its photo's alt text.
        const card = page
            .getByRole('link')
            .filter({ has: page.getByRole('img', { name: / vist[ao] por / }) })
            .first();
        const href = await card.getAttribute('href');
        expect(href).toMatch(/^\/relatos\/\d+$/);
        await card.click();
        await expect(page).toHaveURL(new RegExp(`${href}$`));
        reportUrl = `${baseURL}${href}`;

        const types = Object.values(t.logbook.types).join('|');
        await expect(page.getByRole('heading', { level: 1, name: new RegExp(`^(${types})$`) })).toBeVisible();
        await expect(page.getByText(`${copy.by}:`)).toBeVisible();
        await expect(page.getByText(copy.when, { exact: true })).toBeVisible();
        await expect(page.getByRole('region', { name: copy.mapLabel })).toBeAttached();
        await expect(page.getByRole('heading', { level: 2, name: copy.nearbyTitle })).toBeVisible();
    });

    await test.step('"Mande um postal" offers WhatsApp with the report URL in the text', async () => {
        await expect(page.getByText(copy.postcardTitle, { exact: true })).toBeVisible();
        const whatsapp = page.getByRole('link', { name: copy.whatsapp });
        const href = await whatsapp.getAttribute('href');
        expect(href).not.toBeNull();
        const shared = new URL(href ?? '');
        expect(shared.origin).toBe('https://wa.me');
        expect(shared.searchParams.get('text')).toBe(copy.shareText(reportUrl));
    });

    await test.step('"Copiar link" puts the report URL on the clipboard and says so', async () => {
        await page.getByRole('button', { name: copy.copy, exact: true }).click();
        await expect(page.getByRole('button', { name: copy.copied, exact: true })).toBeVisible();
        expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(reportUrl);
    });
});
