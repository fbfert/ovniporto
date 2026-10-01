<?php

namespace App\Domain\Place\Data;

final readonly class PlaceSpaceCard
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $role,
        public int $phase,
        public string $status,
    ) {}

    /** @return array{slug: string, name: string, role: string, phase: int, status: string} */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'role' => $this->role,
            'phase' => $this->phase,
            'status' => $this->status,
        ];
    }
}
