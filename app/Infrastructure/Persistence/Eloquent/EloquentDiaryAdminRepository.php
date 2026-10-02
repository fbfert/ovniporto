<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Place\Contracts\DiaryAdminRepository;
use App\Domain\Place\Data\DiaryPostDraft;
use App\Models\ConstructionPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class EloquentDiaryAdminRepository implements DiaryAdminRepository
{
    public function all(): array
    {
        return ConstructionPost::query()
            ->orderByRaw('published_at is not null')
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->map(fn (ConstructionPost $p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'phase' => $p->phase,
                'publishedAt' => $p->published_at?->toIso8601String(),
                'cover' => $p->cover_path ? Storage::disk('public')->url($p->cover_path) : null,
            ])
            ->values()
            ->all();
    }

    public function find(int $id): ?array
    {
        $post = ConstructionPost::query()->find($id);

        return $post === null ? null : [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'phase' => $post->phase,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'cover' => $post->cover_path ? Storage::disk('public')->url($post->cover_path) : null,
            'coverAlt' => $post->cover_alt,
        ];
    }

    public function coverOf(int $id): ?string
    {
        return ConstructionPost::query()->find($id)?->cover_path;
    }

    public function create(DiaryPostDraft $post, ?string $coverPath): int
    {
        return ConstructionPost::query()->create([
            ...$this->fields($post),
            'slug' => $this->uniqueSlug($post->title),
            'cover_path' => $coverPath,
        ])->id;
    }

    public function update(int $id, DiaryPostDraft $post, ?string $coverPath): void
    {
        ConstructionPost::query()->findOrFail($id)->update([
            ...$this->fields($post),
            ...($coverPath === null ? [] : ['cover_path' => $coverPath]),
        ]);
    }

    public function delete(int $id): ?string
    {
        $post = ConstructionPost::query()->find($id);
        $post?->delete();

        return $post?->cover_path;
    }

    /** @return array<string, mixed> */
    private function fields(DiaryPostDraft $post): array
    {
        return [
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'phase' => $post->phase,
            'published_at' => $post->publishedAt,
            'cover_alt' => $post->coverAlt,
        ];
    }

    /** The slug is set once, at creation: links already shared keep working after a title edit. */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        for ($i = 2; ConstructionPost::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
