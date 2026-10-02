<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Place\Contracts\ConstructionPostRepository;
use App\Models\ConstructionPost;
use Illuminate\Support\Facades\Storage;

final class EloquentConstructionPostRepository implements ConstructionPostRepository
{
    public function published(): array
    {
        return ConstructionPost::query()
            ->published()
            ->latest('published_at')
            ->get()
            ->map(fn (ConstructionPost $post) => $this->card($post))
            ->values()
            ->all();
    }

    public function findPublished(string $slug): ?array
    {
        $post = ConstructionPost::query()->published()->where('slug', $slug)->first();
        if ($post === null) {
            return null;
        }

        /** @var list<array{path: string, alt?: string}> $gallery */
        $gallery = $post->gallery ?? [];

        return [
            ...$this->card($post),
            'body' => (string) $post->body,
            'gallery' => array_map(fn (array $image) => [
                'url' => $this->url($image['path']) ?? '',
                'alt' => $image['alt'] ?? '',
            ], $gallery),
        ];
    }

    /** @return array{slug: string, title: string, excerpt: ?string, cover: ?string, coverAlt: ?string, phase: int, publishedAt: string} */
    private function card(ConstructionPost $post): array
    {
        return [
            'slug' => (string) $post->slug,
            'title' => (string) $post->title,
            'excerpt' => $post->excerpt,
            'cover' => $this->url($post->cover_path),
            'coverAlt' => $post->cover_alt,
            'phase' => (int) $post->phase,
            'publishedAt' => $post->published_at?->toIso8601String() ?? '',
        ];
    }

    private function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
