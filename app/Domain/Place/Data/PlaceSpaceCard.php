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
        public ?string $concept = null,
        public ?string $description = null,
        /** Set when the concept art was uploaded in the panel (not one of the bundled illustrations). */
        public ?string $conceptUrl = null,
    ) {}

    /** @return array{slug: string, name: string, role: string, phase: int, status: string, concept: ?string, description: ?string, conceptUrl: ?string} */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'role' => $this->role,
            'phase' => $this->phase,
            'status' => $this->status,
            'concept' => $this->concept,
            'description' => $this->description,
            'conceptUrl' => $this->conceptUrl,
        ];
    }
}
