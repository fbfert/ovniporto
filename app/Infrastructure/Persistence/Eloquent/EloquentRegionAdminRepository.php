<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Region\Contracts\RegionAdminRepository;
use App\Domain\Region\Data\PartnerFields;
use App\Models\RegionPartner;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EloquentRegionAdminRepository implements RegionAdminRepository
{
    public function all(): array
    {
        return RegionPartner::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (RegionPartner $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'type' => $p->type,
                'city' => $p->city,
                'consentGivenAt' => $p->consent_given_at?->toDateString(),
                'publishedAt' => $p->published_at?->toIso8601String(),
                'isDemo' => $p->is_demo,
            ])
            ->values()
            ->all();
    }

    public function find(int $id): ?array
    {
        $p = RegionPartner::query()->find($id);

        return $p === null ? null : [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'type' => $p->type,
            'shortDescription' => $p->short_description,
            'city' => $p->city,
            'address' => $p->address,
            'lat' => $p->lat === null ? null : (float) $p->lat,
            'lng' => $p->lng === null ? null : (float) $p->lng,
            'phone' => $p->phone,
            'whatsapp' => $p->whatsapp,
            'instagram' => $p->instagram,
            'website' => $p->website,
            'isFeatured' => $p->is_featured,
            'isDemo' => $p->is_demo,
            'cover' => $p->cover_path ? Storage::disk('public')->url($p->cover_path) : null,
            'consentGivenAt' => $p->consent_given_at?->toDateString(),
            'hasConsentProof' => $p->consent_proof_path !== null,
            'publishedAt' => $p->published_at?->toIso8601String(),
        ];
    }

    public function slugOf(int $id): ?string
    {
        return RegionPartner::query()->find($id, ['id', 'slug'])?->slug;
    }

    public function create(PartnerFields $fields): int
    {
        return RegionPartner::query()->create([
            ...$this->columns($fields),
            'slug' => $this->uniqueSlug($fields->name),
            'sort_order' => (int) RegionPartner::query()->max('sort_order') + 1,
        ])->id;
    }

    public function update(int $id, PartnerFields $fields): void
    {
        RegionPartner::query()->findOrFail($id)->update([
            ...$this->columns($fields),
            ...($fields->consentGivenAt === null ? ['published_at' => null] : []),
        ]);
    }

    public function setCover(int $id, string $path): ?string
    {
        $partner = RegionPartner::query()->findOrFail($id);
        $previous = $partner->cover_path;
        $partner->update(['cover_path' => $path]);

        return $previous;
    }

    public function setConsentProof(int $id, string $path): ?string
    {
        $partner = RegionPartner::query()->findOrFail($id);
        $previous = $partner->consent_proof_path;
        $partner->update(['consent_proof_path' => $path]);

        return $previous;
    }

    public function consentProofOf(int $id): ?string
    {
        return RegionPartner::query()->find($id)?->consent_proof_path;
    }

    public function unpublish(int $id): void
    {
        RegionPartner::query()->whereKey($id)->update(['published_at' => null]);
    }

    public function delete(int $id): array
    {
        $partner = RegionPartner::query()->find($id);
        if ($partner === null) {
            return [];
        }
        $partner->delete();

        return array_values(array_filter([$partner->cover_path, $partner->consent_proof_path]));
    }

    /** @return array<string, mixed> */
    private function columns(PartnerFields $f): array
    {
        return [
            'name' => $f->name,
            'type' => $f->type->value,
            'short_description' => $f->shortDescription,
            'city' => $f->city,
            'address' => $f->address,
            'lat' => $f->lat,
            'lng' => $f->lng,
            'phone' => $f->phone,
            'whatsapp' => $f->whatsapp,
            'instagram' => $f->instagram,
            'website' => $f->website,
            'is_featured' => $f->isFeatured,
            'consent_given_at' => $f->consentGivenAt,
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'parceiro';
        $slug = $base;
        for ($i = 2; RegionPartner::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
