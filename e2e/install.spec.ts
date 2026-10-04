import { expect, test } from '@playwright/test';

interface ManifestIcon {
    src: string;
    sizes: string;
    purpose?: string;
}

test('the site is installable: manifest with the night theme and icons that load', async ({ page, request }) => {
    await page.goto('/');
    const href = await page.locator('link[rel="manifest"]').getAttribute('href');
    expect(href).toBe('/manifest.webmanifest');

    const manifest = (await (await request.get('/manifest.webmanifest')).json()) as Record<string, unknown> & {
        icons: ManifestIcon[];
    };
    expect(manifest).toMatchObject({ display: 'standalone', start_url: '/', theme_color: '#061121', lang: 'pt-BR' });
    expect(manifest.icons.map((icon) => icon.sizes)).toEqual(expect.arrayContaining(['192x192', '512x512']));
    expect(manifest.icons.some((icon) => icon.purpose === 'maskable')).toBe(true);
    for (const icon of manifest.icons) {
        const response = await request.get(icon.src);
        expect(response.ok(), icon.src).toBe(true);
        expect(response.headers()['content-type']).toContain('image/png');
    }
    expect((await request.get('/icons/apple-touch-icon.png')).ok()).toBe(true);
});

test('without network, the site shows "Sem sinal da torre"', async ({ page, context }) => {
    await page.goto('/');
    await page.evaluate(async () => {
        await navigator.serviceWorker.ready;
    });
    await page.reload();
    await expect.poll(() => page.evaluate(() => navigator.serviceWorker.controller !== null)).toBe(true);

    await context.setOffline(true);
    await page.goto('/loja');
    await expect(page.getByRole('heading', { name: 'Sem sinal da torre' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Tentar de novo' })).toBeVisible();
    await context.setOffline(false);
});
