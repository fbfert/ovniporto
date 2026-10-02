<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Models\Sighting;
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
    }

    private function erase(Sighting $sighting): void
    {
        foreach ($sighting->photos as $photo) {
            foreach (self::PHOTO_DISKS as $disk) {
                Storage::disk($disk)->delete((string) $photo->path);
            }
        }
        $sighting->photos()->delete();
        $sighting->delete();
    }
}
