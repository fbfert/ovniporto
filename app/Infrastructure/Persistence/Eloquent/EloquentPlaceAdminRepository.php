<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Place\Contracts\PlaceAdminRepository;
use App\Models\PlaceSpace;
use App\Models\SitePhoto;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class EloquentPlaceAdminRepository implements PlaceAdminRepository
{
    public function spaces(): array
    {
        return PlaceSpace::query()
            ->orderBy('phase')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PlaceSpace $s) => [
                'id' => (int) $s->getKey(),
                'slug' => (string) $s->getAttribute('slug'),
                'name' => (string) $s->getAttribute('name'),
                'role' => (string) $s->getAttribute('role'),
                'description' => $s->getAttribute('description'),
                'phase' => (int) $s->getAttribute('phase'),
                'status' => (string) $s->getAttribute('status'),
                'concept' => $s->getAttribute('concept_image_path'),
                'conceptUrl' => $this->uploadedUrl($s->getAttribute('concept_image_path')),
            ])
            ->values()
            ->all();
    }

    /** Panel uploads are files ("concepts/….webp"); bundled illustrations are manifest slugs. */
    private function uploadedUrl(mixed $concept): ?string
    {
        return is_string($concept) && str_contains($concept, '/') ? Storage::disk('public')->url($concept) : null;
    }

    public function updateSpace(int $id, array $fields): void
    {
        PlaceSpace::query()->whereKey($id)->update($fields);
    }

    public function moveSpace(int $id, int $direction): void
    {
        $phase = PlaceSpace::query()->whereKey($id)->value('phase');
        $this->swap(PlaceSpace::query()->where('phase', $phase), $id, $direction);
    }

    public function setConcept(int $id, string $path): ?string
    {
        $space = PlaceSpace::query()->findOrFail($id);
        $previous = $space->getAttribute('concept_image_path');
        $space->update(['concept_image_path' => $path]);

        return is_string($previous) ? $previous : null;
    }

    public function photos(): array
    {
        return SitePhoto::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (SitePhoto $p) => [
                'id' => $p->id,
                'url' => Storage::disk('public')->url($p->path),
                'alt' => $p->alt,
                'caption' => $p->caption,
                'takenAt' => $p->taken_at?->toDateString(),
            ])
            ->values()
            ->all();
    }

    public function addPhoto(string $path, string $alt, ?string $caption, ?DateTimeImmutable $takenAt): int
    {
        return SitePhoto::query()->create([
            'path' => $path,
            'alt' => $alt,
            'caption' => $caption,
            'taken_at' => $takenAt?->format('Y-m-d'),
            'sort_order' => (int) SitePhoto::query()->max('sort_order') + 1,
        ])->id;
    }

    public function updatePhoto(int $id, string $alt, ?string $caption, ?DateTimeImmutable $takenAt): void
    {
        SitePhoto::query()->whereKey($id)->update([
            'alt' => $alt,
            'caption' => $caption,
            'taken_at' => $takenAt?->format('Y-m-d'),
        ]);
    }

    public function deletePhoto(int $id): ?string
    {
        $photo = SitePhoto::query()->find($id);
        $photo?->delete();

        return $photo?->path;
    }

    public function movePhoto(int $id, int $direction): void
    {
        $this->swap(SitePhoto::query(), $id, $direction);
    }

    /**
     * Renumbers the group 0..n with the item swapped with its neighbour.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $group
     */
    private function swap(Builder $group, int $id, int $direction): void
    {
        DB::transaction(function () use ($group, $id, $direction) {
            /** @var list<int> $ids */
            $ids = (clone $group)->orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn ($v) => (int) $v)->all();
            $at = array_search($id, $ids, true);
            if ($at === false) {
                return;
            }
            $to = $at + ($direction < 0 ? -1 : 1);
            if (! isset($ids[$to])) {
                return;
            }
            [$ids[$at], $ids[$to]] = [$ids[$to], $ids[$at]];
            foreach ($ids as $order => $itemId) {
                (clone $group)->whereKey($itemId)->update(['sort_order' => $order]);
            }
        });
    }
}
