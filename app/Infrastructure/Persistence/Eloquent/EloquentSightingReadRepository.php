<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Region\Distance;
use App\Domain\Sightings\Contracts\SightingReadRepository;
use App\Domain\Sightings\Data\SightingCard;
use App\Domain\Sightings\Data\SightingFilters;
use App\Infrastructure\Sightings\SightingPhotoUrls;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentSightingReadRepository implements SightingReadRepository
{
    public function countApproved(): int
    {
        return Sighting::query()->approved()->count();
    }

    public function latestApproved(int $limit): array
    {
        return $this->page(new SightingFilters, 1, $limit);
    }

    public function countPublic(SightingFilters $filters): int
    {
        return $this->filtered($filters)->count();
    }

    public function pins(SightingFilters $filters): array
    {
        return $this->filtered($filters)
            ->with('photos')
            ->get()
            ->map(fn (Sighting $s) => [
                'id' => $s->id,
                'type' => $s->type->value,
                'lat' => round($s->lat, 5),
                'lng' => round($s->lng, 5),
                'date' => $s->observed_date->toDateString(),
                'nickname' => $s->public_nickname,
                'thumb' => $this->firstPhotoUrl($s, 400),
            ])
            ->values()
            ->all();
    }

    public function page(SightingFilters $filters, int $page, int $perPage): array
    {
        return $this->filtered($filters)
            ->with('photos')
            ->forPage(max(1, $page), $perPage)
            ->get()
            ->map(fn (Sighting $s) => $this->card($s))
            ->values()
            ->all();
    }

    public function findPublic(int $id): ?array
    {
        $sighting = Sighting::query()->approved()->with('photos')->find($id);

        return $sighting === null ? null : $this->detail($sighting, signed: false);
    }

    public function findForAuthor(int $id, int $memberId): ?array
    {
        $sighting = Sighting::query()->with('photos')->where('member_id', $memberId)->find($id);

        return $sighting === null ? null : $this->detail($sighting, signed: true);
    }

    public function nearby(float $lat, float $lng, int $radiusKm, int $excludeId, int $limit): array
    {
        // Cheap bounding box in SQL, exact great-circle distance in PHP.
        $dLat = $radiusKm / 111.0;
        $dLng = $radiusKm / (111.0 * max(0.1, cos(deg2rad($lat))));

        return Sighting::query()
            ->approved()
            ->with('photos')
            ->whereKeyNot($excludeId)
            ->whereBetween('lat', [$lat - $dLat, $lat + $dLat])
            ->whereBetween('lng', [$lng - $dLng, $lng + $dLng])
            ->get()
            ->map(fn (Sighting $s) => ['sighting' => $s, 'km' => Distance::km($lat, $lng, $s->lat, $s->lng)])
            ->filter(fn (array $row) => $row['km'] <= $radiusKm)
            ->sortBy('km')
            ->take($limit)
            ->map(fn (array $row) => $this->card($row['sighting']))
            ->values()
            ->all();
    }

    /** @return Builder<Sighting> approved and published, inside the period and type, newest first */
    private function filtered(SightingFilters $filters): Builder
    {
        $since = $filters->since(new DateTimeImmutable('today'));

        return Sighting::query()
            ->approved()
            ->when($since !== null, fn (Builder $q) => $q->where('observed_date', '>=', $since?->format('Y-m-d')))
            ->when($filters->type !== null, fn (Builder $q) => $q->where('type', $filters->type))
            ->latest('published_at')
            ->orderByDesc('id');
    }

    private function card(Sighting $sighting): SightingCard
    {
        return new SightingCard(
            id: $sighting->id,
            type: $sighting->type,
            placeLabel: $sighting->place_label,
            observedDate: DateTimeImmutable::createFromInterface($sighting->observed_date),
            nickname: $sighting->public_nickname,
            photoUrl: $this->firstPhotoUrl($sighting, 800),
        );
    }

    private function firstPhotoUrl(Sighting $sighting, int $width): ?string
    {
        $photo = $sighting->photos->first();

        return $photo instanceof SightingPhoto ? SightingPhotoUrls::public($photo, $width) : null;
    }

    /** @return array<string, mixed> */
    private function detail(Sighting $sighting, bool $signed): array
    {
        return [
            'id' => $sighting->id,
            'type' => $sighting->type->value,
            'description' => $sighting->description,
            'observedDate' => $sighting->observed_date->toDateString(),
            'timeRange' => $sighting->observed_time_range,
            'exactTime' => $sighting->observed_time_kind === 'exact' ? substr((string) $sighting->observed_time, 0, 5) : null,
            'lat' => round($sighting->lat, 5),
            'lng' => round($sighting->lng, 5),
            'place' => $sighting->place_label,
            'gaze' => $sighting->gaze_direction,
            'nickname' => $sighting->public_nickname,
            'status' => $sighting->status->value,
            'publishedAt' => $sighting->published_at?->toDateString(),
            'photos' => $sighting->photos
                ->map(fn (SightingPhoto $photo) => [
                    'thumb' => $signed ? SightingPhotoUrls::signed($photo, 400) : SightingPhotoUrls::public($photo, 400),
                    'full' => $signed ? SightingPhotoUrls::signed($photo, 1600) : SightingPhotoUrls::public($photo, 1600),
                ])
                ->filter(fn (array $urls) => $urls['full'] !== null)
                ->values()
                ->all(),
        ];
    }
}
