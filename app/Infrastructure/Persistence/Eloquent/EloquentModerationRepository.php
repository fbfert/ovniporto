<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Sightings\Contracts\ModerationRepository;
use App\Domain\Sightings\Data\ModerationDecision;
use App\Domain\Sightings\SightingStatus;
use App\Infrastructure\Sightings\SightingPhotoUrls;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentModerationRepository implements ModerationRepository
{
    public function countByStatus(): array
    {
        $counts = array_fill_keys(array_map(fn (SightingStatus $s) => $s->value, SightingStatus::cases()), 0);
        Sighting::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->toBase()
            ->get()
            ->each(function (object $row) use (&$counts) {
                /** @var object{status: string, total: int|string} $row */
                $counts[$row->status] = (int) $row->total;
            });

        return $counts;
    }

    public function queue(SightingStatus $status, int $page, int $perPage): array
    {
        return $this->ordered($status)
            ->with('photos')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn (Sighting $s) => [
                'id' => $s->id,
                'type' => $s->type->value,
                'nickname' => $s->public_nickname,
                'observedDate' => $s->observed_date->toDateString(),
                'city' => $s->approx_city ?? $s->place_label,
                'submittedAt' => ($s->submitted_at ?? $s->created_at)->toIso8601String(),
                'thumb' => $this->firstPhoto($s),
                'photoCount' => $s->photos->count(),
            ])
            ->values()
            ->all();
    }

    public function review(int $id): ?array
    {
        $sighting = Sighting::query()->with(['photos', 'member'])->find($id);
        if ($sighting === null) {
            return null;
        }
        $author = $sighting->member;

        return [
            'id' => $sighting->id,
            'status' => $sighting->status->value,
            'type' => $sighting->type->value,
            'description' => $sighting->description,
            'observedDate' => $sighting->observed_date->toDateString(),
            'timeRange' => $sighting->observed_time_kind === 'range' ? $sighting->observed_time_range : null,
            'exactTime' => $sighting->observed_time_kind === 'exact' ? substr((string) $sighting->observed_time, 0, 5) : null,
            'lat' => round($sighting->lat, 5),
            'lng' => round($sighting->lng, 5),
            'gaze' => $sighting->gaze_direction,
            'nickname' => $sighting->public_nickname,
            'city' => $sighting->approx_city,
            'placeLabel' => $sighting->place_label,
            'moderationNote' => $sighting->moderation_note,
            'submittedAt' => ($sighting->submitted_at ?? $sighting->created_at)->toIso8601String(),
            'publishedAt' => $sighting->published_at?->toIso8601String(),
            'consentAt' => $sighting->consent_given_at->toIso8601String(),
            'photos' => $sighting->photos
                ->map(fn (SightingPhoto $photo) => [
                    'thumb' => SightingPhotoUrls::signed($photo, 400),
                    'full' => SightingPhotoUrls::signed($photo, 1600),
                    'processing' => $photo->variants === null && $photo->processed_at === null && ! str_starts_with($photo->path, 'demo/'),
                ])
                ->values()
                ->all(),
            // Real name and e-mail exist only on this screen, for moderators.
            'author' => $author === null ? null : [
                'name' => $author->name,
                'email' => $author->email,
                'memberSince' => $author->created_at->toDateString(),
                'reports' => Sighting::query()->where('member_id', $author->id)->count(),
            ],
        ];
    }

    public function neighbours(int $id): array
    {
        $status = $this->statusOf($id);
        if ($status === null) {
            return ['previous' => null, 'next' => null];
        }
        /** @var list<int> $ids */
        $ids = $this->ordered($status)->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
        $at = array_search($id, $ids, true);
        if ($at === false) {
            return ['previous' => null, 'next' => null];
        }

        return ['previous' => $ids[$at - 1] ?? null, 'next' => $ids[$at + 1] ?? null];
    }

    public function statusOf(int $id): ?SightingStatus
    {
        return Sighting::query()->find($id, ['id', 'status'])?->status;
    }

    public function snapshot(int $id): ?array
    {
        $sighting = Sighting::query()->find($id);

        return $sighting === null ? null : [
            'status' => $sighting->status->value,
            'moderationNote' => $sighting->moderation_note,
            'publishedAt' => $sighting->published_at?->toIso8601String(),
        ];
    }

    public function apply(int $id, ModerationDecision $decision, int $moderatorId, DateTimeImmutable $now): void
    {
        // Through the model, so the observer bumps the public caches (map, API, home).
        Sighting::query()->findOrFail($id)->update([
            'status' => $decision->status,
            'moderation_note' => $decision->noteToAuthor,
            'moderated_by' => $moderatorId,
            'moderated_at' => $now,
            'published_at' => $decision->publish ? $now : null,
        ]);
    }

    public function oldestPendingSince(): ?DateTimeImmutable
    {
        $sighting = $this->ordered(SightingStatus::Pending)->first();

        return $sighting === null ? null : DateTimeImmutable::createFromInterface($sighting->submitted_at ?? $sighting->created_at);
    }

    /** @return Builder<Sighting> */
    private function ordered(SightingStatus $status): Builder
    {
        $query = Sighting::query()->where('status', $status);

        return $status === SightingStatus::Pending
            ? $query->orderByRaw('coalesce(submitted_at, created_at) asc')->orderBy('id')
            : $query->orderByRaw('coalesce(moderated_at, created_at) desc')->orderByDesc('id');
    }

    private function firstPhoto(Sighting $sighting): ?string
    {
        $photo = $sighting->photos->first();

        return $photo instanceof SightingPhoto ? SightingPhotoUrls::signed($photo, 400) : null;
    }
}
