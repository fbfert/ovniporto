<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

#[Signature('og:check {url : Page to fetch, as a link preview robot would (no JavaScript)}')]
#[Description('Print the preview tags WhatsApp and other apps will read from a page, and check its image')]
class CheckOgPreview extends Command
{
    private const SHOWN = [
        'title', 'description', 'canonical', 'robots',
        'og:title', 'og:description', 'og:url', 'og:image', 'og:type', 'twitter:card',
    ];

    private const REQUIRED = ['title', 'og:title', 'og:description', 'og:image'];

    private const IMAGE_SIZE = [1200, 630];

    public function handle(): int
    {
        $url = (string) $this->argument('url');

        try {
            $response = Http::withUserAgent('WhatsApp/2.23 (ovniporto og:check)')->timeout(15)->get($url);
        } catch (ConnectionException $e) {
            $this->error("Não consegui abrir {$url}: {$e->getMessage()}");

            return self::FAILURE;
        }
        if (! $response->successful()) {
            $this->error("{$url} respondeu {$response->status()}.");

            return self::FAILURE;
        }

        $tags = $this->previewTags($response->body());
        $this->table(['Tag', 'Conteúdo'], array_map(fn (string $key) => [$key, $tags[$key] ?? '—'], self::SHOWN));

        $missing = array_values(array_filter(self::REQUIRED, fn (string $key) => ($tags[$key] ?? '') === ''));
        if ($missing !== []) {
            $this->error('Faltando: '.implode(', ', $missing));

            return self::FAILURE;
        }

        return $this->checkImage($tags['og:image']) ? self::SUCCESS : self::FAILURE;
    }

    /** @return array<string, string> */
    private function previewTags(string $html): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);

        $tags = ['title' => trim((string) $dom->getElementsByTagName('title')->item(0)?->textContent)];
        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $key = $meta->getAttribute('property') ?: $meta->getAttribute('name');
            if ($key !== '' && ! isset($tags[$key])) {
                $tags[$key] = $meta->getAttribute('content');
            }
        }
        foreach ($dom->getElementsByTagName('link') as $link) {
            if ($link->getAttribute('rel') === 'canonical') {
                $tags['canonical'] = $link->getAttribute('href');
            }
        }

        return $tags;
    }

    private function checkImage(string $url): bool
    {
        try {
            $image = Http::timeout(15)->get($url);
        } catch (ConnectionException) {
            $this->error("A imagem não abre: {$url}");

            return false;
        }

        $size = $image->successful() ? @getimagesizefromstring($image->body()) : false;
        if ($size === false) {
            $this->error("A imagem não abre ou não é uma imagem ({$image->status()}): {$url}");

            return false;
        }

        [$width, $height] = $size;
        if ([$width, $height] !== self::IMAGE_SIZE) {
            $this->warn("Imagem com {$width}×{$height}; o WhatsApp recorta melhor 1200×630.");
        } else {
            $this->info("Imagem ok: {$width}×{$height}.");
        }

        return true;
    }
}
