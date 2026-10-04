import type { Page } from '@playwright/test';
import { expect } from './expect';
import { resolve } from 'node:path';
import { longDate, t } from '../../resources/js/i18n/pt-BR';

/** A real JPEG with EXIF (Make, DateTimeOriginal 2026-09-30 21:14, GPS in Lages), XMP and IPTC; built by tests/Support/JpegWithExif.php. */
export const PHOTO_WITH_EXIF = resolve('e2e/fixtures/ceu-com-exif.jpg');

/** Byte sequences that only exist when a metadata block survived (mirrors JpegWithExif::SIGNATURES). */
export const METADATA_SIGNATURES = [
    'Exif',
    'http://ns.adobe.com/xap/1.0/',
    'Photoshop 3.0',
    '8BIM',
    'OVNICAM-EXIF-MARKER',
    '2026:09:30 21:14:05',
    'OVNI-XMP-MARKER',
    'OVNI-IPTC-MARKER',
] as const;

interface ReportInput {
    description: string;
    /** With a photo, date, time and point come from its EXIF suggestions; without, the point is tapped on the map. */
    photo?: string;
}

const report = t.report;

async function next(page: Page, nextTitle: string): Promise<void> {
    await page.getByRole('button', { name: report.next, exact: true }).click();
    await expect(page.getByRole('heading', { level: 1, name: nextTitle })).toBeVisible();
}

/** Walks the 4-step wizard at /relatar as the signed-in member and sends the report to the tower. */
export async function submitReport(page: Page, { description, photo }: ReportInput): Promise<void> {
    await page.goto('/relatar');

    // 1 · what
    await expect(page.getByRole('heading', { level: 1, name: report.what.title })).toBeVisible();
    await page.getByRole('radio', { name: report.what.types.light, exact: true }).click();
    await page.getByLabel(report.what.descriptionLabel).fill(description);
    await next(page, report.photos.title);

    // 2 · photos (optional)
    if (photo) {
        await page.getByLabel(report.photos.add).setInputFiles(photo);
        await expect(page.getByRole('img', { name: 'Foto 1', exact: true })).toBeVisible();
        await next(page, report.when.title);
    } else {
        await page.getByRole('button', { name: report.skip, exact: true }).click();
        await expect(page.getByRole('heading', { level: 1, name: report.when.title })).toBeVisible();
    }

    // 3 · when and where
    if (photo) {
        // The EXIF of the original is read in the browser and only *offered*; each offer is accepted explicitly.
        await page
            .getByText(report.when.suggestDate(`${longDate('2026-09-30')}, 21:14`))
            .locator('..')
            .getByRole('button', { name: report.when.useSuggestion, exact: true })
            .click();
        await expect(page.getByLabel(report.when.exactLabel, { exact: true })).toHaveValue('21:14');
        await page
            .getByText(report.when.suggestPoint)
            .locator('..')
            .getByRole('button', { name: report.when.useSuggestion, exact: true })
            .click();
        await expect(page.getByText(report.when.fromPhoto)).toHaveCount(2);
    } else {
        await page.getByRole('radio', { name: report.when.ranges[1]!.label, exact: true }).click();
        // Tap the Leaflet map (centered on Lages) to drop the pin.
        const map = page.getByRole('region', { name: report.when.mapLabel });
        await expect(map.locator('.leaflet-container, .leaflet-pane').first()).toBeAttached();
        const box = await map.boundingBox();
        if (!box) throw new Error('the point picker map has no box');
        await map.click({ position: { x: box.width / 2, y: Math.min(box.height / 2, 120) } });
        await expect(map.getByRole('button', { name: report.when.mapLabel })).toBeVisible();
    }
    await next(page, report.review.title);

    // 4 · review, consent and send
    await expect(page.getByText(description)).toBeVisible();
    await page.getByLabel(report.review.consent).check();
    await page.getByRole('button', { name: report.send, exact: true }).click();
    // The e2e server processes photos synchronously (sync queue, GD: every width in JPEG, WebP and AVIF): slow on purpose.
    await expect(page).toHaveURL(/\/relatar\/enviado$/, { timeout: 90_000 });
    await expect(page.getByRole('heading', { level: 1, name: report.sent.title })).toBeVisible();
}

/** As a moderator: opens the pending report by `nickname` from the queue, ticks the checklist and approves it. Returns its id. */
export async function approvePendingReportOf(page: Page, nickname: string): Promise<number> {
    await page.goto('/painel/relatos?aba=pendentes');
    await expect(page.getByRole('heading', { level: 1, name: t.panel.queue.title })).toBeVisible();
    await page.getByRole('link', { name: new RegExp(`@${nickname}\\b`) }).click();
    await expect(page).toHaveURL(/\/painel\/relatos\/\d+$/);
    const id = Number(new URL(page.url()).pathname.split('/').pop());

    await expect(page.getByText(`@${nickname}`, { exact: true })).toBeVisible();
    for (const item of t.panel.review.checklist) {
        await page.getByLabel(item).check();
    }
    await page.getByRole('button', { name: t.panel.review.approve, exact: true }).click();
    await expect(page.getByText('Relato aprovado e publicado no Livro.')).toBeVisible();
    return id;
}

/** Drops the session cookies: the page is a guest again. */
export async function signOut(page: Page): Promise<void> {
    await page.context().clearCookies();
}
