<?php

namespace App\Console\Commands;

use App\Infrastructure\Content\SitemapFile;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sitemap:generate')]
#[Description('Write sitemap.xml with the public pages and every published content')]
class GenerateSitemap extends Command
{
    public function handle(SitemapFile $sitemap): int
    {
        $this->info('sitemap.xml gerado em '.$sitemap->write());

        return self::SUCCESS;
    }
}
