import type { Page } from '@playwright/test';

/** Mirrors database/seeders/E2eSeeder.php. */
export const MEMBERS = {
    author: { id: 1, nickname: 'coruja_e2e' },
    moderator: { id: 2, nickname: 'torre_e2e' },
    admin: { id: 3, nickname: 'admin_e2e' },
    leaving: { id: 4, nickname: 'saida_e2e' },
} as const;

/** The fake of the Google sign-in: the local-only /dev/entrar-como route. */
export async function signInAs(page: Page, member: (typeof MEMBERS)[keyof typeof MEMBERS]): Promise<void> {
    await page.goto(`/dev/entrar-como/${member.id}`);
    await page.waitForURL('**/conta');
}
