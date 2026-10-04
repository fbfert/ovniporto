<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Content\Contracts\PublishedContentIndex;
use App\Domain\Content\Sharing\SitemapEntry;
use App\Domain\Region\PartnerPublication;
use App\Models\ConstructionPost;
use App\Models\Product;
use App\Models\RegionPartner;
use App\Models\Sighting;

final class EloquentPublishedContentIndex implements PublishedContentIndex
{
    public function entries(): array
    {
        return [
            ...Sighting::query()->approved()->orderBy('id')->get(['id', 'published_at'])
                ->map(fn (Sighting $s) => new SitemapEntry("/relatos/{$s->id}", $s->published_at))->all(),
            ...Product::query()->where('is_active', true)->orderBy('id')->get(['slug', 'updated_at'])
                ->map(fn (Product $p) => new SitemapEntry("/loja/{$p->slug}", $p->updated_at))->all(),
            ...RegionPartner::query()->whereNotNull('consent_given_at')->whereNotNull('published_at')->orderBy('id')
                ->get(['slug', 'consent_given_at', 'published_at', 'updated_at'])
                ->filter(fn (RegionPartner $p) => PartnerPublication::isPublic($p->consent_given_at, $p->published_at, now()))
                ->map(fn (RegionPartner $p) => new SitemapEntry("/regiao/{$p->slug}", $p->updated_at))->values()->all(),
            ...ConstructionPost::query()->published()->orderBy('id')->get(['slug', 'published_at', 'updated_at'])
                ->map(fn (ConstructionPost $p) => new SitemapEntry("/obra/{$p->slug}", $p->updated_at ?? $p->published_at))->all(),
        ];
    }
}
