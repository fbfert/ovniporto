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
                ...$this->contentOf($submission),
                'consent_given_at' => $now,
                'submitted_at' => $now,
                'status' => SightingStatus::Pending,
            ]);

            return ['sightingId' => $sighting->id, 'photoIds' => $this->attachPhotos($sighting, $photoPaths, 0)];
        });
    }

    public function findOwned(int $memberId, int $sightingId): ?array
    {
        $sighting = Sighting::query()->where('member_id', $memberId)->find($sightingId);
        if ($sighting === null) {
            return null;
        }

        /** @var list<int> $photoIds */
        $photoIds = $sighting->photos()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        return ['status' => $sighting->status, 'photoIds' => $photoIds];
    }

    public function resubmit(int $sightingId, SightingSubmission $submission, array $keptPhotoIds, array $newPhotoPaths, DateTimeInterface $now): array
    {
        return DB::transaction(function () use ($sightingId, $submission, $keptPhotoIds, $newPhotoPaths, $now) {
            $sighting = Sighting::query()->findOrFail($sightingId);
            $sighting->update([
                ...$this->contentOf($submission),
                'consent_given_at' => $now,
                'submitted_at' => $now,
                'status' => SightingStatus::Pending,
                'moderation_note' => null,
                'published_at' => null,
                'approx_city' => null,
            ]);

            $removed = $sighting->photos()->whereNotIn('id', $keptPhotoIds)->get();
            $sighting->photos()->whereNotIn('id', $keptPhotoIds)->delete();
            foreach ($keptPhotoIds as $order => $photoId) {
                $sighting->photos()->whereKey($photoId)->update(['sort_order' => $order]);
            }

            return [
                'photoIds' => $this->attachPhotos($sighting, $newPhotoPaths, count($keptPhotoIds)),
                'removed' => $removed
                    ->map(fn (SightingPhoto $p) => ['path' => $p->path, 'variants' => $p->variants ?? []])
                    ->values()
                    ->all(),
            ];
        });
    }

    public function pointOf(int $sightingId): ?array
    {
        $sighting = Sighting::query()->find($sightingId, ['id', 'lat', 'lng']);

        return $sighting === null ? null : ['lat' => $sighting->lat, 'lng' => $sighting->lng];
    }

    public function setApproxCity(int $sightingId, ?string $city): void
    {
        // A plain query: the panel-only city must not bump the public caches.
        Sighting::query()->whereKey($sightingId)->update(['approx_city' => $city]);
    }

    /** @return array<string, mixed> */
    private function contentOf(SightingSubmission $submission): array
    {
        return [
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
        ];
    }

    /**
     * @param  list<string>  $paths
     * @return list<int>
     */
    private function attachPhotos(Sighting $sighting, array $paths, int $firstOrder): array
    {
        $photoIds = [];
        foreach ($paths as $i => $path) {
            $photoIds[] = $sighting->photos()->create([
                'path' => $path, 'width' => 0, 'height' => 0, 'sort_order' => $firstOrder + $i,
            ])->id;
        }

        return $photoIds;
    }

    public function findPhoto(int $photoId): ?array
    {
        $photo = SightingPhoto::query()->find($photoId);

        return $photo === null ? null : ['path' => $photo->path, 'sightingId' => $photo->sighting_id];
    }

    public function markPhotoProcessed(int $photoId, string $basePath, int $width, int $height, array $variantWidths, bool $avif = false): void
    {
        SightingPhoto::query()->whereKey($photoId)->update([
            'path' => $basePath,
            'width' => $width,
            'height' => $height,
            'variants' => json_encode($variantWidths),
            'avif' => $avif,
            'processed_at' => now(),
        ]);
    }

    public function deletePhoto(int $photoId): void
    {
        SightingPhoto::query()->whereKey($photoId)->delete();
    }
}
