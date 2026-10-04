<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Orders\Contracts\OrderRepository;

/** Account deletion, Orders side: anonymized and kept for tax law, then purged (privacy:purge-orders). */
final readonly class OrdersContentEraser implements MemberContentEraser
{
    public function __construct(private OrderRepository $orders) {}

    public function eraseFor(int $memberId, string $email): void
    {
        $this->orders->anonymizeFor($memberId, $email, (int) config('privacy.fiscal_retention_years'));
    }
}
