<?php

namespace App\Domain\Origin;

/** What kind of evidence a statement of the Cachi dossier rests on. Always shown as words, never only as a colour. */
enum SourceKind: string
{
    case Document = 'document';
    case Voice = 'voice';
    case Report = 'report';
    case Archive = 'archive';
    case Ordinary = 'ordinary';
    case Press = 'press';

    public function label(): string
    {
        return match ($this) {
            self::Document => 'documento oficial',
            self::Voice => 'depoimento direto',
            self::Report => 'relato de fenômeno',
            self::Archive => 'arquivo histórico',
            self::Ordinary => 'hipótese ordinária',
            self::Press => 'imprensa',
        };
    }
}
