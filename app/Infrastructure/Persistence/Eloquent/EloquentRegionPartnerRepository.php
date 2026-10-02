<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Region\Data\PartnerCard;
use App\Domain\Region\PartnerPublication;
use App\Domain\Region\PartnerType;
use App\Models\RegionPartner;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final class EloquentRegionPartnerRepository implements RegionPartnerRepository
{
    public function featuredPublished(int $limit): array
    {
        return $this->publishedQuery()
            ->where('is_featured', true)
            ->limit($limit)
            ->get()
            ->filter(fn (RegionPartner $p) => $this->isPublic($p))
            ->map(fn (RegionPartner $p) => new PartnerCard(
                name: $p->name,
                slug: $p->slug,
                type: $p->type,
                city: $p->city,
                coverUrl: $this->url($p->cover_path),
                isExample: $p->is_demo,
            ))
            ->values()
            ->all();
    }

    public function published(?PartnerType $type = null, ?string $search = null): array
    {
        $query = $this->publishedQuery();
        if ($type !== null) {
            $query->where('type', $type->value);
        }
        if ($search !== null && trim($search) !== '') {
            $term = '%'.addcslashes(trim($search), '%_\\').'%';
            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('short_description', 'like', $term));
        }

        return $query->get()
            ->filter(fn (RegionPartner $p) => $this->isPublic($p))
            ->map(fn (RegionPartner $p) => $this->listing($p))
            ->values()
            ->all();
    }

    public function countPublished(): int
    {
        return $this->publishedQuery()->count();
    }

    public function findPublished(string $slug): ?array
    {
        $partner = $this->publishedQuery()->where('slug', $slug)->first();
        if ($partner === null || ! $this->isPublic($partner)) {
            return null;
        }

        /** @var list<array{path: string, alt?: string}> $gallery */
        $gallery = $partner->gallery ?? [];

        return [
            ...$this->listing($partner),
            'address' => $partner->address,
            'phone' => $partner->phone,
            'whatsapp' => $partner->whatsapp,
            'instagram' => $partner->instagram,
            'website' => $partner->website,
            'gallery' => array_map(fn (array $image) => [
                'url' => $this->url($image['path']) ?? '',
                'alt' => $image['alt'] ?? $partner->name,
            ], $gallery),
        ];
    }

    public function findForPublication(string $slug): ?array
    {
        $partner = RegionPartner::query()->where('slug', $slug)->first();

        return $partner === null ? null : ['slug' => $partner->slug, 'consentGivenAt' => $partner->consent_given_at];
    }

    public function markPublished(string $slug, DateTimeInterface $at): void
    {
        RegionPartner::query()->where('slug', $slug)->update(['published_at' => $at]);
    }

    /**
     * The "published" scope every public read starts from: consent recorded, publication date passed.
     *
     * @return Builder<RegionPartner>
     */
    private function publishedQuery(): Builder
    {
        return RegionPartner::query()
            ->whereNotNull('consent_given_at')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /** Second check through the domain rule, so the query and the rule can never drift apart. */
    private function isPublic(RegionPartner $partner): bool
    {
        return PartnerPublication::isPublic($partner->consent_given_at, $partner->published_at, now());
    }

    /** @return array{name: string, slug: string, type: string, city: string, shortDescription: ?string, cover: ?string, lat: ?float, lng: ?float, isExample: bool} */
    private function listing(RegionPartner $partner): array
    {
        return [
            'name' => $partner->name,
            'slug' => $partner->slug,
            'type' => $partner->type,
            'city' => $partner->city,
            'shortDescription' => $partner->short_description,
            'cover' => $this->url($partner->cover_path),
            'lat' => $partner->lat === null ? null : (float) $partner->lat,
            'lng' => $partner->lng === null ? null : (float) $partner->lng,
            'isExample' => $partner->is_demo,
        ];
    }

    private function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
