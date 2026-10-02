<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberContentEraser;
use App\Domain\Orders\Contracts\OrderRepository;

/** Orders are not deleted (tax law keeps them): they lose the name, contact and street. */
final readonly class OrdersContentEraser implements MemberContentEraser
{
    public function __construct(private OrderRepository $orders) {}

    public function eraseFor(int $memberId): void
    {
        $this->orders->anonymizeFor($memberId);
    }
}
