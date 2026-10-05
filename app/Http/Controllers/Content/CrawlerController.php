<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\BuildLlmsText;
use App\Http\Controllers\Controller;
use App\Infrastructure\Content\SitemapFile;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** What search engines read before anything else. */
class CrawlerController extends Controller
{
    /** Kept out of every index; outside production (staging) the whole site is. */
    private const DISALLOWED = ['/painel', '/conta', '/checkout', '/pedido/', '/relatar', '/boas-vindas', '/entrar', '/dev/'];

    public function sitemap(SitemapFile $sitemap): BinaryFileResponse
    {
        return response()->file($sitemap->current(), [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $rules = app()->isProduction()
            ? array_map(fn (string $path) => "Disallow: {$path}", self::DISALLOWED)
            : ['Disallow: /'];
        $sitemap = rtrim((string) config('app.url'), '/').'/sitemap.xml';

        return response(implode("\n", ['User-agent: *', ...$rules, '', "Sitemap: {$sitemap}", '']), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /** Plain-text guide for AI assistants, led by the Cachi dossier. */
    public function llms(BuildLlmsText $llms): Response
    {
        return response($llms->execute(rtrim((string) config('app.url'), '/')), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
