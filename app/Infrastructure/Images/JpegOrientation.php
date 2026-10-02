<?php

namespace App\Infrastructure\Images;

/**
 * Reads only the EXIF Orientation tag (0x0112) from JPEG bytes, without the
 * exif extension. Everything else in the EXIF block is ignored and, after
 * re-encoding, gone.
 */
final class JpegOrientation
{
    /** @return int 1..8 (1 = upright); 1 when absent or unreadable */
    public static function read(string $jpeg): int
    {
        if (! str_starts_with($jpeg, "\xFF\xD8")) {
            return 1;
        }

        $offset = 2;
        $length = strlen($jpeg);
        while ($offset + 4 <= $length && $jpeg[$offset] === "\xFF") {
            $marker = ord($jpeg[$offset + 1]);
            $size = unpack('n', substr($jpeg, $offset + 2, 2))[1] ?? 0;
            if ($marker === 0xE1 && substr($jpeg, $offset + 4, 6) === "Exif\0\0") {
                return self::fromTiff(substr($jpeg, $offset + 10, $size - 8));
            }
            if ($marker === 0xDA || $size < 2) {
                break; // image data starts: no EXIF before it
            }
            $offset += 2 + $size;
        }

        return 1;
    }

    private static function fromTiff(string $tiff): int
    {
        $format = match (substr($tiff, 0, 2)) {
            'II' => ['v', 'V'],
            'MM' => ['n', 'N'],
            default => null,
        };
        if ($format === null || strlen($tiff) < 8) {
            return 1;
        }
        [$short, $long] = $format;

        $ifd = unpack($long, substr($tiff, 4, 4))[1] ?? 0;
        $entries = unpack($short, substr($tiff, $ifd, 2))[1] ?? 0;
        for ($i = 0; $i < $entries; $i++) {
            $entry = $ifd + 2 + $i * 12;
            if ((unpack($short, substr($tiff, $entry, 2))[1] ?? 0) === 0x0112) {
                $value = unpack($short, substr($tiff, $entry + 8, 2))[1] ?? 1;

                return $value >= 1 && $value <= 8 ? $value : 1;
            }
        }

        return 1;
    }
}
