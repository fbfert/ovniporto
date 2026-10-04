<?php

namespace App\Domain\Sightings\Data;

use App\Domain\Sightings\SightingType;
use DateTimeImmutable;

final readonly class SightingCard
{
    public function __construct(
        public int $id,
        public SightingType $type,
        public ?string $placeLabel,
        public DateTimeImmutable $observedDate,
        public string $nickname,
        public ?string $photoUrl,
        /** @var array{webp: string, avif: ?string, placeholder: ?string, width: int, height: int}|null */
        public ?array $photoSources = null,
    ) {}

    /** @return array{id: int, type: string, place: string|null, date: string, nickname: string, photo: string|null, photoSources: array<string, mixed>|null} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'place' => $this->placeLabel,
            'date' => $this->observedDate->format('Y-m-d'),
            'nickname' => $this->nickname,
            'photo' => $this->photoUrl,
            'photoSources' => $this->photoSources,
        ];
    }
}
