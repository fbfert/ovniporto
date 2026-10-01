<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Models\NewsletterSubscriber;

final class EloquentWaitlistRepository implements WaitlistRepository
{
    public function addIfAbsent(string $email, string $source, string $consentText): ?int
    {
        $subscriber = NewsletterSubscriber::query()->firstOrCreate(
            ['email' => $email],
            ['source' => $source, 'consent_text' => $consentText, 'consented_at' => now()],
        );

        return $subscriber->wasRecentlyCreated ? $subscriber->id : null;
    }

    public function confirm(int $id): bool
    {
        $subscriber = NewsletterSubscriber::query()->find($id);
        if ($subscriber === null) {
            return false;
        }

        $subscriber->confirmed_at ??= now();
        $subscriber->save();

        return true;
    }
}
