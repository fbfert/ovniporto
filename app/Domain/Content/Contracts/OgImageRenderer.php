<?php

namespace App\Domain\Content\Contracts;

use App\Domain\Content\Sharing\OgCard;

/** Draws the 1200×630 preview of a card and keeps it cached by card version. */
interface OgImageRenderer
{
    /** Absolute path of the JPEG for this exact card version (rendered on first request). */
    public function render(OgCard $card): string;
}
