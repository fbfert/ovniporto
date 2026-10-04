<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Domain\Sightings\SightingPhotoFiles;
use App\Infrastructure\Sightings\SightingPhotoUrls;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use Illuminate\Support\Facades\Storage;

final class EloquentMemberSightingRepository implements MemberSightingRepository
{
    /** Pending photos live on the private disk, published ones on the public disk: clean both. */
    private const PHOTO_DISKS = ['public', 'local'];

    public function ownedBy(int $memberId): array
    {
        return Sighting::query()
            ->where('member_id', $memberId)
            ->latest()
            ->get()
            ->map(fn (Sighting $s) => [
                'id' => $s->id,
                'type' => $s->type->value,
                'status' => $s->status->value,
                'moderationNote' => $s->moderation_note,
                'observedDate' => $s->observed_date->toDateString(),
                'placeLabel' => $s->place_label,
                'createdAt' => $s->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    public function deleteOwned(int $memberId, int $sightingId): bool
    {
        $sighting = Sighting::query()->where('member_id', $memberId)->find($sightingId);
        if ($sighting === null) {
            return false;
        }
        $this->erase($sighting);

        return true;
    }

    public function deleteAllOf(int $memberId): void
    {
        Sighting::query()->where('member_id', $memberId)->get()->each(fn (Sighting $s) => $this->erase($s));
        // Photos uploaded but never attached to a report (their rows go with the member).
        Storage::disk('local')->deleteDirectory("uploads/{$memberId}");
    }

    public function draftOf(int $memberId, int $sightingId): ?array
    {
        $sighting = Sighting::query()->with('photos')->where('member_id', $memberId)->find($sightingId);
        if ($sighting === null) {
            return null;
        }

        return [
            'id' => $sighting->id,
            'status' => $sighting->status->value,
            'type' => $sighting->type->value,
            'description' => $sighting->description,
            'observedDate' => $sighting->observed_date->toDateString(),
            'timeRange' => $sighting->observed_time_kind === 'range' ? $sighting->observed_time_range : null,
            'exactTime' => $sighting->observed_time_kind === 'exact' ? substr((string) $sighting->observed_time, 0, 5) : null,
            'lat' => round($sighting->lat, 6),
            'lng' => round($sighting->lng, 6),
            'gaze' => $sighting->gaze_direction,
            'nickname' => $sighting->public_nickname,
            'moderationNote' => $sighting->moderation_note,
            'photos' => $sighting->photos
                ->map(fn (SightingPhoto $photo) => ['id' => $photo->id, 'thumb' => SightingPhotoUrls::signed($photo, 400)])
                ->values()
                ->all(),
        ];
    }

    /** The photo and every WebP variant (a processed photo's path is the base the variants hang from). */
    private function erase(Sighting $sighting): void
    {
        foreach ($sighting->photos as $photo) {
            $files = SightingPhotoFiles::all((string) $photo->path, $photo->variants ?? []);
            foreach (self::PHOTO_DISKS as $disk) {
                Storage::disk($disk)->delete($files);
            }
        }
        $sighting->photos()->delete();
        $sighting->delete();
    }
}
