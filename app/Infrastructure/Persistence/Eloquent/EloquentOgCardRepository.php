<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Content\Contracts\OgCardRepository;
use App\Domain\Content\Sharing\OgCard;
use App\Domain\Content\Sharing\OgKind;
use App\Domain\Region\PartnerPublication;
use App\Infrastructure\Sightings\SightingPhotoUrls;
use App\Models\ConstructionPost;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\RegionPartner;
use App\Models\Sighting;
use App\Models\SightingPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Each query uses the same "public" rule as the page it previews. */
final class EloquentOgCardRepository implements OgCardRepository
{
    private const PHOTO_WIDTH = 1200;

    public function find(OgKind $kind, string $key): ?OgCard
    {
        return match ($kind) {
            OgKind::Sighting => ctype_digit($key) ? $this->sighting((int) $key) : null,
            OgKind::Product => $this->product($key),
            OgKind::Partner => $this->partner($key),
            OgKind::Post => $this->post($key),
        };
    }

    private function sighting(int $id): ?OgCard
    {
        $sighting = Sighting::query()->approved()->with('photos')->find($id);
        if ($sighting === null) {
            return null;
        }

        $where = $sighting->place_label ?? $sighting->approx_city;
        $photo = $sighting->photos->first();

        return new OgCard(
            OgKind::Sighting,
            (string) $sighting->id,
            $this->text('og.sighting'),
            $this->text("sighting_types.{$sighting->type->value}"),
            implode(' · ', array_filter([$where, $sighting->observed_date->format('d/m/Y')])),
            $photo instanceof SightingPhoto ? $this->sightingPhoto($photo) : null,
        );
    }

    private function product(string $slug): ?OgCard
    {
        $product = Product::query()->where('is_active', true)->where('slug', $slug)->with('images')->first();
        if ($product === null) {
            return null;
        }

        $image = $product->images->first();

        return new OgCard(
            OgKind::Product,
            $product->slug,
            $this->text('og.product'),
            $product->name,
            $product->short_description === null ? null : Str::limit($product->short_description, 90),
            $image instanceof ProductImage ? $this->publicFile($image->path) : null,
        );
    }

    private function partner(string $slug): ?OgCard
    {
        $partner = RegionPartner::query()->where('slug', $slug)->first();
        if ($partner === null || ! PartnerPublication::isPublic($partner->consent_given_at, $partner->published_at, now())) {
            return null;
        }

        return new OgCard(
            OgKind::Partner,
            $partner->slug,
            $this->text('og.partner'),
            $partner->name,
            $partner->city,
            $this->publicFile($partner->cover_path),
        );
    }

    private function post(string $slug): ?OgCard
    {
        $post = ConstructionPost::query()->published()->where('slug', $slug)->first();
        if ($post === null) {
            return null;
        }

        return new OgCard(
            OgKind::Post,
            $post->slug,
            $this->text('og.post'),
            $post->title,
            $post->published_at?->format('d/m/Y'),
            $this->publicFile($post->cover_path),
        );
    }

    private function sightingPhoto(SightingPhoto $photo): ?string
    {
        if ($photo->variants === null || $photo->variants === []) {
            // Seeded demo photos live on the public disk until processed.
            return $photo->processed_at === null && str_starts_with($photo->path, 'demo/')
                ? $this->publicFile($photo->path)
                : null;
        }

        $width = SightingPhotoUrls::closest($photo->variants, self::PHOTO_WIDTH);
        $path = "{$photo->path}-{$width}.webp";

        return Storage::disk('local')->exists($path) ? Storage::disk('local')->path($path) : null;
    }

    private function publicFile(?string $path): ?string
    {
        return $path !== null && Storage::disk('public')->exists($path) ? Storage::disk('public')->path($path) : null;
    }

    private function text(string $key): string
    {
        return (string) trans("seo.{$key}", [], 'pt_BR');
    }
}
