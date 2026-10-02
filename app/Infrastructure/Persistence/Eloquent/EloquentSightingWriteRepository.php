<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Sightings\Contracts\SightingWriteRepository;
use App\Domain\Sightings\Data\SightingSubmission;
use App\Domain\Sightings\SightingStatus;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use App\Models\SightingUpload;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

final class EloquentSightingWriteRepository implements SightingWriteRepository
{
    public function recordUpload(int $memberId, string $path, string $mime, int $size): string
    {
        return SightingUpload::query()->create([
            'member_id' => $memberId, 'path' => $path, 'mime' => $mime, 'size' => $size,
        ])->id;
    }

    public function findUpload(int $memberId, string $uploadId): ?array
    {
        $upload = SightingUpload::query()->where('member_id', $memberId)->find($uploadId);

        return $upload === null ? null : ['id' => $upload->id, 'path' => $upload->path];
    }

    public function deleteUpload(string $uploadId): void
    {
        SightingUpload::query()->whereKey($uploadId)->delete();
    }

    public function staleUploads(DateTimeInterface $before): array
    {
        return SightingUpload::query()
            ->where('created_at', '<', $before)
            ->get()
            ->map(fn (SightingUpload $u) => ['id' => $u->id, 'path' => $u->path])
            ->values()
            ->all();
    }

    public function createPending(int $memberId, SightingSubmission $submission, array $photoPaths, DateTimeInterface $now): array
    {
        return DB::transaction(function () use ($memberId, $submission, $photoPaths, $now) {
            $sighting = Sighting::query()->create([
                'member_id' => $memberId,
                'type' => $submission->type,
                'description' => trim($submission->description),
                'observed_date' => $submission->observedDate->format('Y-m-d'),
                'observed_time_kind' => $submission->timeRange !== null ? 'range' : 'exact',
                'observed_time_range' => $submission->timeRange?->value,
                'observed_time' => $submission->exactTime,
                'lat' => $submission->lat,
                'lng' => $submission->lng,
                'gaze_direction' => $submission->gaze?->value,
                'public_nickname' => $submission->nickname,
                'consent_given_at' => $now,
                'status' => SightingStatus::Pending,
            ]);

            $photoIds = [];
            foreach ($photoPaths as $order => $path) {
                $photoIds[] = $sighting->photos()->create([
                    'path' => $path, 'width' => 0, 'height' => 0, 'sort_order' => $order,
                ])->id;
            }

            return ['sightingId' => $sighting->id, 'photoIds' => $photoIds];
        });
    }

    public function findPhoto(int $photoId): ?array
    {
        $photo = SightingPhoto::query()->find($photoId);

        return $photo === null ? null : ['path' => $photo->path, 'sightingId' => $photo->sighting_id];
    }

    public function markPhotoProcessed(int $photoId, string $basePath, int $width, int $height, array $variantWidths): void
    {
        SightingPhoto::query()->whereKey($photoId)->update([
            'path' => $basePath,
            'width' => $width,
            'height' => $height,
            'variants' => json_encode($variantWidths),
            'processed_at' => now(),
        ]);
    }

    public function deletePhoto(int $photoId): void
    {
        SightingPhoto::query()->whereKey($photoId)->delete();
    }
}
