<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Models\NewsletterSubscriber;

/** "Baixar meus dados", Campaign side: the Avise-me subscription tied to the account e-mail, with its consent. */
final class WaitlistDataSource implements MemberDataSource
{
    public function section(): string
    {
        return 'avise_me';
    }

    public function exportFor(int $memberId, string $email): array
    {
        return NewsletterSubscriber::query()
            ->where('email', $email)
            ->get()
            ->map(fn (NewsletterSubscriber $s) => [
                'email' => $s->email,
                'origem' => $s->source,
                'consentimento' => $s->consent_text,
                'consentiu_em' => $s->consented_at->toIso8601String(),
                'confirmado_em' => $s->confirmed_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
