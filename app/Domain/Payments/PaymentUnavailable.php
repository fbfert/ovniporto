<?php

namespace App\Domain\Payments;

use RuntimeException;

/** The payment provider could not be reached or refused the request. */
final class PaymentUnavailable extends RuntimeException
{
    public function __construct(string $reason = 'O pagamento está fora do ar agora. Tente de novo em alguns minutos.')
    {
        parent::__construct($reason);
    }
}
