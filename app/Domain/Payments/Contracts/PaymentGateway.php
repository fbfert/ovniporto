<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\Data\CaptureResult;
use App\Domain\Payments\Data\WebhookNotice;

/**
 * Card and Pix through the provider's own browser components: card data goes
 * from the buyer's browser straight to the provider, never through us. The
 * server only creates the order (with its own total) and captures it.
 */
interface PaymentGateway
{
    /** @return string the provider's order id */
    public function createOrder(string $orderNumber, int $totalCents, string $description): string;

    /** Same id twice → same result, never a second charge (the provider call carries an idempotency key). */
    public function capture(string $providerOrderId): CaptureResult;

    /**
     * Null when the signature does not check out with the provider.
     *
     * @param  array<string, string>  $headers
     */
    public function verifyWebhook(array $headers, string $body): ?WebhookNotice;

    /** Gives the whole capture back to the buyer. PaymentUnavailable when the provider refuses. */
    public function refund(string $captureId, int $amountCents, string $orderNumber): void;

    /** Local stand-in: the checkout offers a "simulate approval" button instead of real buttons. */
    public function isSimulated(): bool;
}
