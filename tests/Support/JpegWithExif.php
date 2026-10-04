<?php

namespace Tests\Support;

/**
 * Builds a real JPEG carrying every kind of metadata a phone can leave behind:
 * an EXIF block (camera "Make", orientation, DateTimeOriginal and a GPS IFD with
 * Lages' latitude/longitude), an XMP packet and an IPTC record. Each carries a
 * recognizable marker so tests can prove none of it survives anywhere it's stored.
 */
final class JpegWithExif
{
    public const MAKE = 'OVNICAM-EXIF-MARKER';

    public const TAKEN_AT = '2026:09:30 21:14:05';

    public const XMP_MARKER = 'OVNI-XMP-MARKER';

    public const IPTC_MARKER = 'OVNI-IPTC-MARKER';

    /** Byte sequences that only exist when a metadata block survived. */
    public const SIGNATURES = [
        "Exif\0\0", 'http://ns.adobe.com/xap/1.0/', 'Photoshop 3.0', '8BIM',
        self::MAKE, self::TAKEN_AT, self::XMP_MARKER, self::IPTC_MARKER,
    ];

    public static function make(int $width = 1200, int $height = 900, int $orientation = 1): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, 20, 38, 68));
        imagefilledellipse($image, (int) ($width / 3), (int) ($height / 3), 60, 60, (int) imagecolorallocate($image, 173, 219, 161));
        ob_start();
        imagejpeg($image, null, 90);
        $jpeg = (string) ob_get_clean();

        $segments = self::segment("\xE1", "Exif\0\0".self::tiff($orientation))
            .self::segment("\xE1", "http://ns.adobe.com/xap/1.0/\0".self::xmp())
            .self::segment("\xED", "Photoshop 3.0\0".self::iptc());

        // Insert right after the SOI marker.
        return substr($jpeg, 0, 2).$segments.substr($jpeg, 2);
    }

    private static function segment(string $marker, string $payload): string
    {
        return "\xFF".$marker.pack('n', strlen($payload) + 2).$payload;
    }

    /** Little-endian TIFF: IFD0 with Make, Orientation, Exif and GPS pointers; Exif IFD with DateTimeOriginal; GPS IFD with lat/lng. */
    private static function tiff(int $orientation): string
    {
        $make = self::MAKE."\0";
        $taken = self::TAKEN_AT."\0";
        $ifd0Offset = 8;
        $ifd0Entries = 4;
        $ifd0Size = 2 + $ifd0Entries * 12 + 4;
        $makeOffset = $ifd0Offset + $ifd0Size;
        $exifOffset = $makeOffset + strlen($make);
        $exifSize = 2 + 12 + 4;
        $takenOffset = $exifOffset + $exifSize;
        $gpsOffset = $takenOffset + strlen($taken);
        $gpsEntries = 4;
        $gpsSize = 2 + $gpsEntries * 12 + 4;
        $latOffset = $gpsOffset + $gpsSize;
        $lngOffset = $latOffset + 24;

        $entry = fn (int $tag, int $type, int $count, string $value) => pack('vvV', $tag, $type, $count).str_pad($value, 4, "\0");
        $rational = fn (int $num, int $den) => pack('VV', $num, $den);

        $ifd0 = pack('v', $ifd0Entries)
            .$entry(0x010F, 2, strlen($make), pack('V', $makeOffset))       // Make (ASCII)
            .$entry(0x0112, 3, 1, pack('v', $orientation))                 // Orientation
            .$entry(0x8769, 4, 1, pack('V', $exifOffset))                  // Exif IFD pointer
            .$entry(0x8825, 4, 1, pack('V', $gpsOffset))                   // GPS IFD pointer
            .pack('V', 0);

        $exif = pack('v', 1)
            .$entry(0x9003, 2, strlen($taken), pack('V', $takenOffset))    // DateTimeOriginal
            .pack('V', 0);

        $gps = pack('v', $gpsEntries)
            .$entry(0x0001, 2, 2, "S\0")                                   // GPSLatitudeRef
            .$entry(0x0002, 5, 3, pack('V', $latOffset))                   // GPSLatitude
            .$entry(0x0003, 2, 2, "W\0")                                   // GPSLongitudeRef
            .$entry(0x0004, 5, 3, pack('V', $lngOffset))                   // GPSLongitude
            .pack('V', 0);

        $lat = $rational(27, 1).$rational(48, 1).$rational(5760, 100);    // 27°48'57.6"
        $lng = $rational(50, 1).$rational(19, 1).$rational(3360, 100);    // 50°19'33.6"

        return 'II'.pack('vV', 42, $ifd0Offset).$ifd0.$make.$exif.$taken.$gps.$lat.$lng;
    }

    private static function xmp(): string
    {
        return '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'
            .'<rdf:Description xmlns:exif="http://ns.adobe.com/exif/1.0/" exif:GPSLatitude="27,48.96S" exif:GPSLongitude="50,19.56W">'
            .'<exif:UserComment>'.self::XMP_MARKER.'</exif:UserComment></rdf:Description></rdf:RDF></x:xmpmeta>';
    }

    /** Photoshop image resource 0x0404 holding one IPTC dataset (2:120, caption). */
    private static function iptc(): string
    {
        $caption = self::IPTC_MARKER;
        $record = "\x1C\x02\x78".pack('n', strlen($caption)).$caption;
        if (strlen($record) % 2 === 1) {
            $record .= "\0";
        }

        return '8BIM'.pack('n', 0x0404)."\0\0".pack('N', strlen($record)).$record;
    }
}
