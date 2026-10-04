import { test, type APIRequestContext } from '@playwright/test';
import { expect, FLOW_TIMEOUT } from '../support/expect';
import { t } from '../../resources/js/i18n/pt-BR';
import { MEMBERS, signInAs } from '../support/members';
import { approvePendingReportOf, signOut, submitReport } from '../support/reports';

const copy = t.members;
const DESCRIPTION = 'Três pontos de luz em fila sobre a Coxilha Rica, sem som nenhum, por uns dez segundos.';

async function publicPinIds(request: APIRequestContext): Promise<number[]> {
    const response = await request.get('/api/sightings?periodo=all');
    expect(response.status()).toBe(200);
    const body = (await response.json()) as { data: { id: number }[] };
    return body.data.map((pin) => pin.id);
}

test('a member downloads their data, deletes the account and their approved report leaves the map', async ({
    page,
}) => {
    // Submitting runs the report's sync jobs (reverse geocoding included) inside the request.
    test.setTimeout(FLOW_TIMEOUT);
    let id = 0;

    await test.step('the leaving member has an approved report on the map', async () => {
        await signInAs(page, MEMBERS.leaving);
        await submitReport(page, { description: DESCRIPTION });
        await signOut(page);
        await signInAs(page, MEMBERS.moderator);
        id = await approvePendingReportOf(page, MEMBERS.leaving.nickname);
        await signOut(page);

        const response = await page.goto(`/relatos/${id}`);
        expect(response?.status()).toBe(200);
        await expect(page.getByText(DESCRIPTION)).toBeVisible();
        expect(await publicPinIds(page.request)).toContain(id);
    });

    await test.step('"Baixar meus dados" confirms the request (the file goes by e-mail)', async () => {
        await signInAs(page, MEMBERS.leaving);
        await page.goto('/conta?aba=privacidade');
        await expect(page.getByRole('heading', { level: 2, name: copy.exportTitle })).toBeVisible();
        await page.getByRole('button', { name: copy.exportCta, exact: true }).click();
        await expect(page.getByText('Pedido recebido: o arquivo chega no seu e-mail em alguns minutos.')).toBeVisible();
    });

    await test.step('deleting the account asks for the nickname and signs the member out', async () => {
        await page.getByRole('button', { name: copy.deleteCta, exact: true }).click();
        const dialog = page.getByRole('dialog', { name: copy.deleteConfirmTitle });
        await expect(dialog).toBeVisible();
        await expect(dialog.getByText(copy.deleteConfirmLead(MEMBERS.leaving.nickname))).toBeVisible();

        await dialog.getByLabel(copy.deleteConfirmLabel).fill('outro_apelido');
        await dialog.getByRole('button', { name: copy.deleteConfirmCta, exact: true }).click();
        await expect(dialog.getByText('O apelido não confere. Nada foi excluído.')).toBeVisible();

        await dialog.getByLabel(copy.deleteConfirmLabel).fill(MEMBERS.leaving.nickname);
        await dialog.getByRole('button', { name: copy.deleteConfirmCta, exact: true }).click();
        await expect(page).toHaveURL(/\/$/);
        await expect(page.getByText('Conta excluída. Guardei um lugar pra você, se quiser voltar.')).toBeVisible();
    });

    await test.step('a guest no longer finds the report: page 404, out of the map API and the Livro', async () => {
        await signOut(page);
        const response = await page.goto(`/relatos/${id}`);
        expect(response?.status()).toBe(404);
        await expect(page.getByRole('heading', { name: t.errors[404]!.title })).toBeVisible();

        expect(await publicPinIds(page.request)).not.toContain(id);

        await page.goto('/mapa');
        await expect(page.getByRole('heading', { level: 1, name: t.logbookPage.title })).toBeVisible();
        await expect(page.locator(`a[href="/relatos/${id}"]`)).toHaveCount(0);
    });
});
