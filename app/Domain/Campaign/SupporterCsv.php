<?php

namespace App\Domain\Campaign;

use App\Domain\Campaign\Data\SupporterEntry;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Reads the supporters spreadsheet exported from the crowdfunding platform,
 * after the admin trims it to these columns (header required, comma or
 * semicolon): nome, valor, recompensa, publicar_nome, data.
 * A name is only ever published with "sim" in publicar_nome.
 */
final class SupporterCsv
{
    public const COLUMNS = ['nome', 'valor', 'recompensa', 'publicar_nome', 'data'];

    private const YES = ['sim', 's', 'yes', 'y', '1', 'true'];

    /** @return list<SupporterEntry> */
    public static function parse(string $csv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim(self::withoutBom($csv))) ?: [];
        if ($lines === [] || trim($lines[0]) === '') {
            throw new InvalidArgumentException('O arquivo está vazio.');
        }

        $delimiter = substr_count($lines[0], ';') > substr_count($lines[0], ',') ? ';' : ',';
        $header = array_map(fn ($c) => mb_strtolower(trim((string) $c)), str_getcsv($lines[0], $delimiter, '"', ''));
        $index = array_flip($header);
        if (! isset($index['nome'])) {
            throw new InvalidArgumentException('A primeira linha precisa ter a coluna "nome".');
        }

        $entries = [];
        foreach (array_slice($lines, 1) as $i => $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line, $delimiter, '"', '');
            $cell = fn (string $column) => isset($index[$column]) ? trim((string) ($row[$index[$column]] ?? '')) : '';
            $entries[] = self::entry($cell, $i + 2);
        }

        return $entries;
    }

    /** @param callable(string): string $cell */
    private static function entry(callable $cell, int $lineNumber): SupporterEntry
    {
        $name = $cell('nome');
        if ($name === '') {
            throw new InvalidArgumentException("Linha {$lineNumber}: falta o nome.");
        }

        return new SupporterEntry(
            name: mb_substr($name, 0, 120),
            amountCents: self::cents($cell('valor'), $lineNumber),
            reward: $cell('recompensa') === '' ? null : mb_substr($cell('recompensa'), 0, 120),
            publishName: in_array(mb_strtolower($cell('publicar_nome')), self::YES, true),
            supportedAt: self::date($cell('data'), $lineNumber),
        );
    }

    /** "1.234,56", "1234.56", "R$ 50" → cents. */
    private static function cents(string $value, int $lineNumber): ?int
    {
        $value = trim(str_ireplace('r$', '', $value));
        if ($value === '') {
            return null;
        }
        if (str_contains($value, ',')) {
            $value = str_replace(['.', ','], ['', '.'], $value);
        }
        if (! is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException("Linha {$lineNumber}: valor inválido.");
        }

        return (int) round((float) $value * 100);
    }

    /** "2027-03-01" or "01/03/2027". */
    private static function date(string $value, int $lineNumber): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }
        foreach (['!Y-m-d', '!d/m/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            if ($date !== false) {
                return $date;
            }
        }

        throw new InvalidArgumentException("Linha {$lineNumber}: data inválida (use 2027-03-01 ou 01/03/2027).");
    }

    private static function withoutBom(string $text): string
    {
        return str_starts_with($text, "\u{FEFF}") ? substr($text, 3) : $text;
    }
}
