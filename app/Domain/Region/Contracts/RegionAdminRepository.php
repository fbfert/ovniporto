<?php

namespace App\Domain\Region\Contracts;

use App\Domain\Region\Data\PartnerFields;

/** /painel/regiao: every partner, published or not, with its consent record. */
interface RegionAdminRepository
{
    /** @return list<array{id: int, name: string, slug: string, type: string, city: string, consentGivenAt: ?string, publishedAt: ?string, isDemo: bool}> */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    public function slugOf(int $id): ?string;

    public function create(PartnerFields $fields): int;

    /** Removing the consent date also takes the partner off the site. */
    public function update(int $id, PartnerFields $fields): void;

    /** @return string|null the previous file, to erase */
    public function setCover(int $id, string $path): ?string;

    /** @return string|null the previous proof, to erase */
    public function setConsentProof(int $id, string $path): ?string;

    public function consentProofOf(int $id): ?string;

    public function unpublish(int $id): void;

    /** @return list<string> files to erase (cover, proof) */
    public function delete(int $id): array;
}
