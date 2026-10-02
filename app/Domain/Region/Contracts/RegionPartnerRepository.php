<?php

namespace App\Domain\Region\Contracts;

use App\Domain\Region\Data\PartnerCard;
use App\Domain\Region\PartnerType;
use DateTimeInterface;

/** Every public read goes through the "published" rule (see PartnerPublication). */
interface RegionPartnerRepository
{
    /** @return list<PartnerCard> featured, publishable partners */
    public function featuredPublished(int $limit): array;

    /**
     * Publishable partners, optionally narrowed by type and by a text search on name, city and description.
     *
     * @return list<array{name: string, slug: string, type: string, city: string, shortDescription: ?string, cover: ?string, lat: ?float, lng: ?float, isExample: bool}>
     */
    public function published(?PartnerType $type = null, ?string $search = null): array;

    public function countPublished(): int;

    /** @return array{name: string, slug: string, type: string, city: string, shortDescription: ?string, cover: ?string, lat: ?float, lng: ?float, isExample: bool, address: ?string, phone: ?string, whatsapp: ?string, instagram: ?string, website: ?string, gallery: list<array{url: string, alt: string}>}|null */
    public function findPublished(string $slug): ?array;

    /** @return array{slug: string, consentGivenAt: ?DateTimeInterface}|null */
    public function findForPublication(string $slug): ?array;

    public function markPublished(string $slug, DateTimeInterface $at): void;
}
