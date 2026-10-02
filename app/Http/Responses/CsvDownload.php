<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** A CSV the spreadsheet apps open with accents intact (UTF-8 with BOM, semicolons for pt-BR Excel). */
final class CsvDownload
{
    /** @param list<list<string>> $rows */
    public static function make(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\u{FEFF}");
            foreach ($rows as $row) {
                fputcsv($out, $row, ';', '"', '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
