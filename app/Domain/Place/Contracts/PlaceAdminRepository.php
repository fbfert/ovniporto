<?php

namespace App\Domain\Place\Contracts;

use DateTimeImmutable;

/** /painel/lugar: planned spaces, terrain photos and their order. */
interface PlaceAdminRepository
{
    /** @return list<array{id: int, slug: string, name: string, role: string, description: ?string, phase: int, status: string, concept: ?string, conceptUrl: ?string}> by phase, then order */
    public function spaces(): array;

    /** @param array{name: string, role: string, description: ?string, phase: int, status: string} $fields */
    public function updateSpace(int $id, array $fields): void;

    public function moveSpace(int $id, int $direction): void;

    /** @return string|null the previous concept, to erase when it was an upload */
    public function setConcept(int $id, string $path): ?string;

    /** @return list<array{id: int, url: string, alt: string, caption: ?string, takenAt: ?string}> */
    public function photos(): array;

    public function addPhoto(string $path, string $alt, ?string $caption, ?DateTimeImmutable $takenAt): int;

    public function updatePhoto(int $id, string $alt, ?string $caption, ?DateTimeImmutable $takenAt): void;

    /** @return string|null the file path to erase */
    public function deletePhoto(int $id): ?string;

    public function movePhoto(int $id, int $direction): void;
}
