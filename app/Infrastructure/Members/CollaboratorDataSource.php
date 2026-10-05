<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Models\ResearchCollaborator;

/** "Baixar meus dados", Origin side: offers to help the research sent with the account e-mail. */
final class CollaboratorDataSource implements MemberDataSource
{
    public function section(): string
    {
        return 'colaboracao';
    }

    public function exportFor(int $memberId, string $email): array
    {
        return ResearchCollaborator::query()
            ->where('email', mb_strtolower($email))
            ->get()
            ->map(fn (ResearchCollaborator $c) => [
                'nome' => $c->name,
                'email' => $c->email,
                'cidade_pais' => $c->location,
                'como_ajudar' => $c->areas,
                'mensagem' => $c->message,
                'consentimento' => $c->consent_text,
                'consentiu_em' => $c->consented_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
