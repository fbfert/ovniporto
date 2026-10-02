<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Models\Sighting;

/** "Baixar meus dados", Sightings side: every report of the member with its consent date. */
final class SightingsDataSource implements MemberDataSource
{
    public function section(): string
    {
        return 'relatos';
    }

    public function exportFor(int $memberId, string $email): array
    {
        return Sighting::query()
            ->with('photos')
            ->where('member_id', $memberId)
            ->orderBy('id')
            ->get()
            ->map(fn (Sighting $s) => [
                'tipo' => $s->type->value,
                'descricao' => $s->description,
                'data_observada' => $s->observed_date->toDateString(),
                'local' => ['lat' => $s->lat, 'lng' => $s->lng, 'rotulo' => $s->place_label],
                'apelido_publico' => $s->public_nickname,
                'status' => $s->status->value,
                'consentimento_publicacao_em' => $s->consent_given_at->toIso8601String(),
                'publicado_em' => $s->published_at?->toIso8601String(),
                'fotos' => $s->photos->count(),
            ])
            ->values()
            ->all();
    }
}
