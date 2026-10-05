<?php

namespace App\Domain\Origin;

/** The A–F confidence scale of the Atlas research dossier. */
enum ConfidenceGrade: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';

    /** What the grade stands for, as worded in the dossier. */
    public function meaning(): string
    {
        return match ($this) {
            self::A => 'fonte primária ou ato oficial',
            self::B => 'fonte institucional ou documentação secundária muito sólida',
            self::C => 'imprensa ou bibliografia com informação verificável',
            self::D => 'relato testemunhal ou entrevista de protagonista',
            self::E => 'tradição local ou interpretação ufológica',
            self::F => 'informação não confirmada',
        };
    }

    /**
     * A seal such as "A", "A/B" or "B/C" (the dossier joins two grades when the evidence is mixed).
     *
     * @return list<self>
     */
    public static function parseSeal(string $seal): array
    {
        $grades = [];
        foreach (explode('/', strtoupper(trim($seal))) as $part) {
            $grade = self::tryFrom(trim($part));
            if ($grade === null) {
                throw new InvalidOriginData("Grau de confiança inválido: \"{$seal}\"");
            }
            $grades[] = $grade;
        }

        return $grades;
    }
}
