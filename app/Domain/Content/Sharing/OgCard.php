<?php

namespace App\Domain\Content\Sharing;

/**
 * What a preview image shows: never more than the public page already shows.
 * The version is a hash of everything drawn, so any edit yields a new image URL
 * (WhatsApp caches previews by URL).
 */
final readonly class OgCard
{
    public function __construct(
        public OgKind $kind,
        public string $key,
        public string $eyebrow,
        public string $title,
        public ?string $subtitle = null,
        /** Absolute path of a local image file, or null for the drawn night scene. */
        public ?string $photo = null,
    ) {}

    public function version(): string
    {
        return substr(sha1(implode("\n", [
            $this->kind->value, $this->key, $this->eyebrow, $this->title, $this->subtitle ?? '', $this->photo ?? '',
        ])), 0, 10);
    }
}
