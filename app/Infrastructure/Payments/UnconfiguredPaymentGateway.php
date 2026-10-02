<?php

namespace App\Infrastructure\Payments;

use App\Domain\Payments\Contracts\PaymentGateway;
use App\Domain\Payments\Data\CaptureResult;
use App\Domain\Payments\Data\WebhookNotice;
use App\Domain\Payments\PaymentUnavailable;

/** Production without PayPal credentials: nobody can pay, and the checkout says so plainly. */
final class UnconfiguredPaymentGateway implements PaymentGateway
{
    public function createOrder(string $orderNumber, int $totalCents, string $description): string
    {
        throw new PaymentUnavailable('O pagamento ainda está sendo configurado. Volte em breve.');
    }

    public function capture(string $providerOrderId): CaptureResult
    {
        throw new PaymentUnavailable('O pagamento ainda está sendo configurado. Volte em breve.');
    }

    public function verifyWebhook(array $headers, string $body): ?WebhookNotice
    {
        return null;
    }

    public function isSimulated(): bool
    {
        return false;
    }
}
