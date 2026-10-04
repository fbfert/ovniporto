import { expect, test } from '@playwright/test';
import { MEMBERS, signInAs } from './support/members';

test('the home opens with its title and the demo content', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle('OVNIPORTO Lages · A pista de pouso do planalto');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
});

test('the fake sign-in opens the member account', async ({ page }) => {
    await signInAs(page, MEMBERS.author);
    await expect(page.getByText(`@${MEMBERS.author.nickname}`).first()).toBeVisible();
});
