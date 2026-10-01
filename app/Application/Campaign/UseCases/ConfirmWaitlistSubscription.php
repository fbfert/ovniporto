<?php

namespace App\Application\Campaign\UseCases;

use App\Domain\Campaign\Contracts\WaitlistRepository;

final readonly class ConfirmWaitlistSubscription
{
    public function __construct(private WaitlistRepository $waitlist) {}

    public function execute(int $subscriptionId): bool
    {
        return $this->waitlist->confirm($subscriptionId);
    }
}
