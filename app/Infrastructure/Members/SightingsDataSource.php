<?php

namespace App\Infrastructure\Members;

use App\Domain\Members\Contracts\MemberDataSource;
use App\Domain\Sightings\SightingStatus;
use App\Infrastructure\Sightings\SightingPhotoUrls;
use App\Models\Sighting;
use App\Models\SightingPhoto;

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
                'fotos' => $this->photoLinks($s),
            ])
            ->values()
            ->all();
    }

    /**
     * Approved photos: their public address. Photos still in analysis open only through
     * 10-minute signed URLs, so the export points to the report page, which issues them
     * to the signed-in author whenever the file is opened.
     *
     * @return list<string>
     */
    private function photoLinks(Sighting $sighting): array
    {
        if ($sighting->status !== SightingStatus::Approved || $sighting->published_at === null) {
            return $sighting->photos->isEmpty() ? [] : [route('sightings.show', ['sighting' => $sighting->id])];
        }

        return $sighting->photos
            ->map(fn (SightingPhoto $photo) => SightingPhotoUrls::public($photo, 1600))
            ->filter()
            ->values()
            ->all();
    }
}
