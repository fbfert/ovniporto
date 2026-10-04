<?php

namespace App\Infrastructure\Content;

use App\Application\Content\UseCases\BuildSitemap;

/** sitemap.xml written by the scheduler and served as a ready file. */
final readonly class SitemapFile
{
    public function __construct(
        private BuildSitemap $sitemap,
        private ?string $file = null,
    ) {}

    public function write(): string
    {
        $path = $this->path();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $xml = view('sitemap', [
            'baseUrl' => rtrim((string) config('app.url'), '/'),
            'entries' => $this->sitemap->execute(),
        ])->render();
        file_put_contents($path, $xml, LOCK_EX);

        return $path;
    }

    /** Path of the current file, written on first use if the scheduler hasn't run yet. */
    public function current(): string
    {
        return is_file($this->path()) ? $this->path() : $this->write();
    }

    private function path(): string
    {
        return $this->file ?? storage_path('app/sitemap/sitemap.xml');
    }
}
