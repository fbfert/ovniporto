<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Region\Data\PartnerCard;
use App\Domain\Region\PartnerPublication;
use App\Models\RegionPartner;
use Illuminate\Support\Facades\Storage;

final class EloquentRegionPartnerRepository implements RegionPartnerRepository
{
    public function featuredPublished(int $limit): array
    {
        $now = now();

        return RegionPartner::query()
            ->where('is_featured', true)
            ->whereNotNull('consent_given_at')
            ->where('published_at', '<=', $now)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get()
            ->filter(fn (RegionPartner $p) => PartnerPublication::isPublic($p->consent_given_at, $p->published_at, $now))
            ->map(fn (RegionPartner $p) => new PartnerCard(
                name: $p->name,
                slug: $p->slug,
                type: $p->type,
                city: $p->city,
                coverUrl: $p->cover_path ? Storage::disk('public')->url($p->cover_path) : null,
                isExample: $p->is_demo,
            ))
            ->values()
            ->all();
    }
}
