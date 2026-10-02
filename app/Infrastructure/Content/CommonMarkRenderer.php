<?php

namespace App\Infrastructure\Content;

use App\Domain\Content\Contracts\MarkdownRenderer;
use Illuminate\Support\Str;

/** GitHub-flavoured CommonMark (league/commonmark, shipped with Laravel). */
final class CommonMarkRenderer implements MarkdownRenderer
{
    public function render(string $markdown): string
    {
        return trim(Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]));
    }
}
