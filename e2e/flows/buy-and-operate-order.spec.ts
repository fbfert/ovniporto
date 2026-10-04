import { test, type Page } from '@playwright/test';
import { expect, FLOW_TIMEOUT } from '../support/expect';
import { t } from '../../resources/js/i18n/pt-BR';
import { MEMBERS, signInAs } from '../support/members';
import { signOut } from '../support/reports';

const checkout = t.checkout;
const status = t.order.status;
const actions = t.panel.orders.actions;

const BUYER = { name: 'Compradora E2E', email: 'compradora@e2e.test', phone: '(49) 99988-7766', cpf: '529.982.247-25' };

/** The status badge sits next to the order number heading, on the customer page and in the panel. */
test.describe.configure({ timeout: FLOW_TIMEOUT });

const statusNextToNumber = (page: Page, number: string) =>
    page.getByRole('heading', { level: 1, name: number }).locator('..');

test('"Finalizar compra" in the cart drawer lands on a usable checkout', async ({ page }) => {
    // The drawer lives in the persistent layout: it must close when the visit leaves the page.
    await page.goto('/loja/adesivo-ovniporto');
    await page.getByRole('button', { name: t.storePage.add, exact: true }).click();
    const drawer = page.getByRole('dialog', { name: t.cart.title });
    await drawer.getByRole('link', { name: t.cart.checkout }).click();
    await expect(page).toHaveURL(/\/checkout$/);
    await expect(drawer).toBeHidden();
});

test('a guest buys the sticker for pickup in Lages, pays and the admin takes the order to delivered', async ({
    page,
}) => {
    let number = '';
    let orderUrl = '';

    await test.step('the sticker goes to the cart', async () => {
        await page.goto('/loja/adesivo-ovniporto');
        await expect(page.getByRole('heading', { level: 1, name: /Adesivo OVNIPORTO/i })).toBeVisible();
        await page.getByRole('button', { name: t.storePage.add, exact: true }).click();
        const drawer = page.getByRole('dialog', { name: t.cart.title });
        await expect(drawer).toBeVisible();
        await expect(drawer.getByText(t.cart.count(1))).toBeVisible();
        await drawer.getByRole('link', { name: t.cart.checkout }).click();
        await expect(page).toHaveURL(/\/checkout$/);
        await expect(drawer).toBeHidden();
    });

    await test.step('identification, pickup in Lages and review', async () => {
        await expect(page.getByRole('heading', { level: 1, name: checkout.title })).toBeVisible();
        await page.getByLabel(checkout.name, { exact: true }).fill(BUYER.name);
        await page.getByLabel(checkout.email, { exact: true }).fill(BUYER.email);
        await page.getByLabel(checkout.phone, { exact: true }).fill(BUYER.phone);
        await page.getByLabel(checkout.cpf, { exact: true }).fill(BUYER.cpf);
        await page.getByRole('button', { name: checkout.next, exact: true }).click();

        const pickup = page.getByRole('radio', { name: new RegExp(checkout.pickup) });
        await expect(pickup).toBeVisible();
        await pickup.check();
        await page.getByRole('button', { name: checkout.next, exact: true }).click();

        await expect(page.getByRole('heading', { level: 2, name: checkout.review })).toBeVisible();
        await expect(page.getByText(/1 × Adesivo OVNIPORTO/i)).toBeVisible();
        await expect(page.getByText(checkout.pickupLead)).toBeVisible();
    });

    await test.step('placing the order leads to the payment, approved through the simulated gateway', async () => {
        await page.getByRole('button', { name: checkout.place, exact: true }).click();
        // The buyer's links are signed URLs (no account needed to follow the order).
        await expect(page).toHaveURL(/\/pedido\/OVP-\d{4}-\d{6}\/pagar\?signature=/);
        number = /OVP-\d{4}-\d{6}/.exec(page.url())?.[0] ?? '';
        await expect(page.getByText(t.pay.orderLabel(number))).toBeVisible();

        await page.getByRole('button', { name: t.pay.simulate }).click();
        await expect(page).toHaveURL(new RegExp(`/pedido/${number}\\?signature=`));
        orderUrl = page.url();
        await expect(statusNextToNumber(page, number)).toContainText(status.paid!);
        await expect(page.getByText(t.order.pickup)).toBeVisible();
    });

    await test.step('the admin moves the pickup order: production, out the door, delivered', async () => {
        await signOut(page);
        await signInAs(page, MEMBERS.admin);
        await page.goto(`/painel/pedidos/${number}`);
        const badge = statusNextToNumber(page, number);
        await expect(badge).toContainText(status.paid!);
        // A pickup order never buys a shipping label.
        await expect(page.getByRole('button', { name: actions.label })).toHaveCount(0);

        await page.getByRole('button', { name: actions.production, exact: true }).click();
        await expect(badge).toContainText(status.in_production!);
        await expect(page.getByRole('button', { name: actions.label })).toHaveCount(0);

        await page.getByRole('button', { name: actions.ship, exact: true }).click();
        const dialog = page.getByRole('dialog', { name: t.panel.orders.shipTitle });
        await expect(dialog).toBeVisible();
        await dialog.getByRole('button', { name: t.panel.orders.confirm, exact: true }).click();
        await expect(dialog).toBeHidden();
        await expect(badge).toContainText(status.shipped!);

        await page.getByRole('button', { name: actions.deliver, exact: true }).click();
        await expect(badge).toContainText(status.delivered!);
        await expect(page.getByRole('button', { name: actions.deliver, exact: true })).toHaveCount(0);
    });

    await test.step("the buyer's order page follows the new status", async () => {
        await signOut(page);
        await page.goto(orderUrl);
        await expect(statusNextToNumber(page, number)).toContainText(status.delivered!);
    });
});
