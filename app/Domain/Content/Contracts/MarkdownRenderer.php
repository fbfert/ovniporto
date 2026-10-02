<?php

namespace App\Domain\Content\Contracts;

interface MarkdownRenderer
{
    /** Markdown to safe HTML: raw HTML is stripped and unsafe links (javascript:, data:) dropped. */
    public function render(string $markdown): string;
}
