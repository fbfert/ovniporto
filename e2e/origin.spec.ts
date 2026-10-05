import { expect, test } from '@playwright/test';

test('the old /lenda lands on the origin, with the founders, the Niva and both doors', async ({ page }) => {
    await page.goto('/lenda');
    await expect(page).toHaveURL(/\/origem$/);

    await expect(page.getByRole('heading', { level: 1, name: 'A origem' })).toBeVisible();
    await expect(page.getByText('Os fundadores visitaram o Ovnipuerto de Cachi', { exact: false })).toBeVisible();
    await expect(
        page.getByText('O carro (Lada Niva) amarelo do Julean foi abduzido (segundo contam...)', { exact: false }),
    ).toBeVisible();
    await expect(page.getByRole('link', { name: /Ler a história de Cachi/ })).toHaveAttribute('href', '/origem/cachi');
    await expect(page.getByRole('link', { name: /Abrir o Atlas/ })).toHaveAttribute('href', '/origem/atlas');
});

test('the yellow car relato waits honestly, with the concept badge and the Avise-me', async ({ page }) => {
    await page.goto('/origem');
    const relato = page.locator('section', { has: page.getByRole('heading', { name: 'O carro amarelo' }) });

    await expect(relato.getByText('O relato do', { exact: true })).toBeVisible();
    await expect(
        relato.getByText('Ainda estamos capturando o relato do Julean que foi abduzido.', { exact: false }),
    ).toBeVisible();
    await expect(relato.getByText('conceito', { exact: true })).toBeVisible();
    await expect(relato.getByRole('button', { name: 'Me avise' })).toBeVisible();
});

test('nothing on the site still calls the page "lenda"', async ({ page }) => {
    for (const path of ['/', '/origem']) {
        await page.goto(path);
        await expect(page.locator('a[href="/lenda"]')).toHaveCount(0);
        await expect(page.getByRole('link', { name: 'A origem' }).first()).toHaveAttribute('href', '/origem');
    }
});

test('Cachi tells its chapters in order and labels the independent survey as such', async ({ page }) => {
    await page.goto('/origem/cachi');

    const ids = await page.locator('main section[id]').evaluateAll((sections) => sections.map((s) => s.id));
    expect(ids.slice(0, 14)).toEqual([
        'prologo',
        'a-noite',
        'estrella',
        'casa-cueva',
        'relatos',
        'desaparecimento',
        'retorno',
        'lenda-vira-lugar',
        'cidade',
        'personagens',
        'linha-do-tempo',
        'galeria',
        'videos',
        'fontes',
    ]);
    await expect(
        page.getByText('pertencem a investigação ufológica independente e são apresentados como tal', { exact: false }),
    ).toBeVisible();
    await expect(page.locator('#relatos').getByText('relato de fenômeno').first()).toBeVisible();
    await expect(page.locator('#galeria').getByRole('link', { name: 'origem e licença' }).first()).toBeVisible();
});

test('the Cachi videos reach YouTube only after "Assistir", through the no-cookie player', async ({ page }) => {
    const thirdParty: string[] = [];
    page.on('request', (request) => {
        if (/youtube|ytimg|google|doubleclick/.test(new URL(request.url()).hostname)) thirdParty.push(request.url());
    });

    await page.goto('/origem/cachi');
    await page.locator('#videos').scrollIntoViewIfNeeded();
    await page.waitForLoadState('networkidle');
    expect(thirdParty).toEqual([]);

    await page
        .locator('#videos')
        .getByRole('button', { name: /Assistir/ })
        .first()
        .click();
    await expect(page.locator('#videos iframe')).toHaveAttribute(
        'src',
        /^https:\/\/www\.youtube-nocookie\.com\/embed\//,
    );
});

test('the Atlas lists its twelve cases as links of their own, beside the map, and three candidates', async ({
    page,
}) => {
    await page.goto('/origem/atlas');

    await expect(page.getByRole('link', { name: /Caso \d{2}/ })).toHaveCount(12);
    await expect(page.getByRole('heading', { name: 'Casos candidatos', exact: true })).toBeVisible();
    await expect(page.getByRole('article')).toHaveCount(3);
});

test('a case shows its grade and sources; Lages stays a project; maps say they are maps', async ({ page }) => {
    await page.goto('/origem/atlas/st-paul');
    await expect(page.getByRole('heading', { level: 1, name: 'St. Paul UFO Landing Pad' })).toBeVisible();
    await expect(page.getByRole('img', { name: /^Grau de confiança A\/B/ }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'The Landing Pad' })).toHaveAttribute('href', /stpaul\.ca/);
    await expect(page.getByRole('link', { name: /Ängelholm UFO Memorial/ })).toHaveAttribute(
        'href',
        '/origem/atlas/angelholm',
    );

    await page.goto('/origem/atlas/lages');
    await expect(page.getByRole('img', { name: /^Grau de confiança F/ }).first()).toBeVisible();
    await expect(page.getByText('em planejamento').first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'Conhecer o projeto' })).toHaveAttribute('href', '/o-lugar');

    await page.goto('/origem/atlas/green-river');
    await expect(page.getByText(/^Mapa de localização:/)).toBeVisible();
});

test('an unknown case is a 404', async ({ page }) => {
    const response = await page.goto('/origem/atlas/nao-existe');
    expect(response?.status()).toBe(404);
});
