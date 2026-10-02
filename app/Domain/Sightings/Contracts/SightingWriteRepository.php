<?php

namespace App\Domain\Sightings\Contracts;

use App\Domain\Sightings\Data\SightingSubmission;
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

    /** @param list<int> $variantWidths */
    public function markPhotoProcessed(int $photoId, string $basePath, int $width, int $height, array $variantWidths): void;

    public function deletePhoto(int $photoId): void;
}
