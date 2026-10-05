import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { MEMBERS, signInAs } from './support/members';

const PAGES = ['/', '/mapa', '/loja', '/o-lugar', '/relatar', '/origem', '/origem/cachi', '/origem/atlas', '/origem/atlas/lages'];

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
