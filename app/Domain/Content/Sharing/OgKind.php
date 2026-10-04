<?php

namespace App\Domain\Content\Sharing;

/** Public content that gets its own preview image. The value is the URL segment under /og. */
enum OgKind: string
{
    case Sighting = 'relato';
    case Product = 'produto';
    case Partner = 'parceiro';
    case Post = 'obra';
}
