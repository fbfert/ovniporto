<?php

namespace App\Http\Seo;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Sharing metadata of one page. Built by the server and printed by app.blade.php,
 * so link previews never depend on the SSR process being up.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class Seo implements Arrayable
{
    public const DEFAULT_IMAGE = '/og/default.jpg';

    /** @param list<array<string, mixed>> $jsonLd */
    public function __construct(
        public ?string $title,
        public string $description,
        public string $image = self::DEFAULT_IMAGE,
        public string $type = 'website',
        public bool $indexable = true,
        public array $jsonLd = [],
    ) {}

    /** A page described in lang/pt_BR/seo.php; unknown pages are titled by the site and kept out of the index. */
    public static function forRoute(?string $routeName): self
    {
        // Route names contain dots, so they index the array instead of the translation key.
        /** @var array<string, array{title?: ?string, description?: string, image?: string, index?: bool}> $pages */
        $pages = self::text('pages');
        $page = $routeName === null ? null : ($pages[$routeName] ?? null);

        return new self(
            title: $page['title'] ?? null,
            description: $page['description'] ?? (string) self::text('default_description'),
            image: $page['image'] ?? self::DEFAULT_IMAGE,
            indexable: $page !== null && ($page['index'] ?? true),
        );
    }

    /** @param array<string, mixed> ...$items */
    public function withJsonLd(array ...$items): self
    {
        return new self($this->title, $this->description, $this->image, $this->type, $this->indexable, [...$this->jsonLd, ...array_values($items)]);
    }

    public function noindex(): self
    {
        return new self($this->title, $this->description, $this->image, $this->type, false, $this->jsonLd);
    }

    /** @return array{title: ?string, fullTitle: string, description: string, canonical: string, image: string, type: string, robots: string, jsonLd: list<array<string, mixed>>} */
    public function toArray(): array
    {
        $siteName = (string) self::text('site_name');

        return [
            'title' => $this->title,
            'fullTitle' => $this->title === null ? (string) self::text('home_title') : "{$this->title} · {$siteName}",
            'description' => $this->description,
            'canonical' => self::canonical(),
            'image' => str_starts_with($this->image, 'http') ? $this->image : self::appUrl().$this->image,
            'type' => $this->type,
            'robots' => $this->indexable ? 'index, follow' : 'noindex, nofollow',
            'jsonLd' => $this->jsonLd,
        ];
    }

    /** Built on APP_URL (not on the request), so it stays https behind the proxy and drops the query string. */
    private static function canonical(): string
    {
        $path = trim(request()->path(), '/');

        return self::appUrl().($path === '' ? '' : "/{$path}");
    }

    public static function appUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /** @param array<string, string> $replace */
    public static function text(string $key, array $replace = []): mixed
    {
        return trans("seo.{$key}", $replace, 'pt_BR');
    }
}
