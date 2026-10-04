<?php

namespace App\Domain\Sightings\Contracts;

use App\Domain\Sightings\Data\SightingSubmission;
use App\Domain\Sightings\SightingStatus;
use DateTimeInterface;

interface SightingWriteRepository
{
    /** Keeps an uploaded file's reference until a report claims it; returns the upload id. */
    public function recordUpload(int $memberId, string $path, string $mime, int $size): string;

    /** @return array{id: string, path: string}|null the member's own unclaimed upload */
    public function findUpload(int $memberId, string $uploadId): ?array;

    public function deleteUpload(string $uploadId): void;

    /** @return list<array{id: string, path: string}> unclaimed uploads older than the cutoff */
    public function staleUploads(DateTimeInterface $before): array;

    /**
     * Creates the report in "pending" with its consent date, and one photo row
     * per original file path (in order); returns the report id and photo ids.
     *
     * @param  list<string>  $photoPaths
     * @return array{sightingId: int, photoIds: list<int>}
     */
    public function createPending(int $memberId, SightingSubmission $submission, array $photoPaths, DateTimeInterface $now): array;

    /** @return array{path: string, sightingId: int}|null */
    public function findPhoto(int $photoId): ?array;

    /** @param list<int> $variantWidths widths written as WebP (and as AVIF too when $avif) */
    public function markPhotoProcessed(int $photoId, string $basePath, int $width, int $height, array $variantWidths, bool $avif = false): void;

    public function deletePhoto(int $photoId): void;

    /** @return array{status: SightingStatus, photoIds: list<int>}|null the member's own report */
    public function findOwned(int $memberId, int $sightingId): ?array;

    /**
     * Replaces the content of a report sent back for adjustment and puts it in
     * "pending" again: the kept photos stay (in that order, before the new
     * ones), the others are removed and returned so their files can go too.
     *
     * @param  list<int>  $keptPhotoIds
     * @param  list<string>  $newPhotoPaths
     * @return array{photoIds: list<int>, removed: list<array{path: string, variants: list<int>}>}
     */
    public function resubmit(int $sightingId, SightingSubmission $submission, array $keptPhotoIds, array $newPhotoPaths, DateTimeInterface $now): array;

    /** @return array{lat: float, lng: float}|null */
    public function pointOf(int $sightingId): ?array;

    public function setApproxCity(int $sightingId, ?string $city): void;
}
