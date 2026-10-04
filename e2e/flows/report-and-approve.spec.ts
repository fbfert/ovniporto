import { test } from '@playwright/test';
import { expect, FLOW_TIMEOUT } from '../support/expect';
import { t } from '../../resources/js/i18n/pt-BR';
import { MEMBERS, signInAs } from '../support/members';
import {
    approvePendingReportOf,
    METADATA_SIGNATURES,
    PHOTO_WITH_EXIF,
    signOut,
    submitReport,
} from '../support/reports';

const DESCRIPTION = 'Uma luz verde parada sobre o Morro Grande, depois sumiu de repente para o sul.';

test('a member reports with an EXIF photo, the tower approves and the public photo has no metadata', async ({
    page,
}) => {
    // Photo processing runs inside the request on the single-threaded PHP server.
    test.setTimeout(FLOW_TIMEOUT);

    await test.step('the author goes through the wizard with a photo carrying EXIF and GPS', async () => {
        await signInAs(page, MEMBERS.author);
        await submitReport(page, { description: DESCRIPTION, photo: PHOTO_WITH_EXIF });
    });

    let id = 0;
    await test.step('the report waits in the queue and the moderator approves it', async () => {
        await signOut(page);
        await signInAs(page, MEMBERS.moderator);
        id = await approvePendingReportOf(page, MEMBERS.author.nickname);
    });

    await test.step('a guest sees the approved report and its photo', async () => {
        await signOut(page);
        await page.goto(`/relatos/${id}`);
        await expect(page.getByRole('heading', { level: 1, name: t.logbook.types.light })).toBeVisible();
        await expect(page.getByText(DESCRIPTION)).toBeVisible();
        await expect(page.getByText(`${t.sightingPage.by}: ${MEMBERS.author.nickname}`)).toBeVisible();
        await expect(page.getByText(t.sightingPage.pending)).toHaveCount(0);
    });

    await test.step('every public rendition of the photo is an image without EXIF, XMP or IPTC', async () => {
        const photo = page.getByRole('img', {
            name: new RegExp(`^${t.logbook.types.light} vist[ao] por ${MEMBERS.author.nickname}, foto 1$`),
        });
        await expect(photo).toBeVisible();
        const src = await photo.getAttribute('src');
        const srcsets = await page
            .locator('picture source')
            .evaluateAll((sources) => sources.map((source) => source.getAttribute('srcset') ?? ''));
        const urls = new Set(
            [
                src ?? '',
                ...srcsets.flatMap((set) => set.split(',').map((entry) => entry.trim().split(/\s+/)[0] ?? '')),
            ].filter((url) => url.includes('/fotos/relatos/')),
        );
        expect(src).toContain('/fotos/relatos/');

        for (const url of urls) {
            const response = await page.request.get(url);
            expect(response.status(), url).toBe(200);
            expect(response.headers()['content-type'], url).toMatch(/^image\//);
            const body = await response.body();
            expect(body.length, url).toBeGreaterThan(0);
            for (const signature of METADATA_SIGNATURES) {
                expect(body.includes(Buffer.from(signature, 'latin1')), `${url} carries "${signature}"`).toBe(false);
            }
        }
    });
});
