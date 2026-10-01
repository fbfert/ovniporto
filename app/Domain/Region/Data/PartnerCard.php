<?php

namespace App\Domain\Region\Data;

final readonly class PartnerCard
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $type,
        public string $city,
        public ?string $coverUrl,
        public bool $isExample,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'city' => $this->city,
            'cover' => $this->coverUrl,
            'isExample' => $this->isExample,
        ];
    }
}
