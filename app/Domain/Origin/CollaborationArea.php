<?php

namespace App\Domain\Origin;

/** How someone offers to help the research. The value is what the panel and the CSV show. */
enum CollaborationArea: string
{
    case Research = 'pesquisa';
    case Translation = 'traducao';
    case Photos = 'fotos';
    case Fieldwork = 'vistoria';
    case Other = 'outro';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
