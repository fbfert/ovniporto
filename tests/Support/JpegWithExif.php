<?php

namespace Tests\Support;

/**
 * Builds a real JPEG carrying an EXIF block with orientation and a GPS IFD
 * (latitude/longitude of Lages) plus a recognizable camera "Make" string,
 * so tests can prove nothing of it survives processing.
 */
final class JpegWithExif
{
    public const MAKE = 'OVNICAM-EXIF-MARKER';

    public static function make(int $width = 1200, int $height = 900, int $orientation = 1): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, (int) imagecolorallocate($image, 20, 38, 68));
        imagefilledellipse($image, (int) ($width / 3), (int) ($height / 3), 60, 60, (int) imagecolorallocate($image, 173, 219, 161));
        ob_start();
        imagejpeg($image, null, 90);
        $jpeg = (string) ob_get_clean();

        $app1 = "Exif\0\0".self::tiff($orientation);
        $segment = "\xFF\xE1".pack('n', strlen($app1) + 2).$app1;

        // Insert right after the SOI marker.
        return substr($jpeg, 0, 2).$segment.substr($jpeg, 2);
    }

    /** Little-endian TIFF: IFD0 with Make, Orientation and a GPS IFD pointer; GPS IFD with lat/lng. */
    private static function tiff(int $orientation): string
    {
        $make = self::MAKE."\0";
        $ifd0Offset = 8;
        $ifd0Entries = 3;
        $ifd0Size = 2 + $ifd0Entries * 12 + 4;
        $makeOffset = $ifd0Offset + $ifd0Size;
        $gpsOffset = $makeOffset + strlen($make);
        $gpsEntries = 4;
        $gpsSize = 2 + $gpsEntries * 12 + 4;
        $latOffset = $gpsOffset + $gpsSize;
        $lngOffset = $latOffset + 24;

        $entry = fn (int $tag, int $type, int $count, string $value) => pack('vvV', $tag, $type, $count).str_pad($value, 4, "\0");
        $rational = fn (int $num, int $den) => pack('VV', $num, $den);

        $ifd0 = pack('v', $ifd0Entries)
            .$entry(0x010F, 2, strlen($make), pack('V', $makeOffset))       // Make (ASCII)
            .$entry(0x0112, 3, 1, pack('v', $orientation))                 // Orientation
            .$entry(0x8825, 4, 1, pack('V', $gpsOffset))                   // GPS IFD pointer
            .pack('V', 0);

        $gps = pack('v', $gpsEntries)
            .$entry(0x0001, 2, 2, "S\0")                                   // GPSLatitudeRef
            .$entry(0x0002, 5, 3, pack('V', $latOffset))                   // GPSLatitude
            .$entry(0x0003, 2, 2, "W\0")                                   // GPSLongitudeRef
            .$entry(0x0004, 5, 3, pack('V', $lngOffset))                   // GPSLongitude
            .pack('V', 0);

        $lat = $rational(27, 1).$rational(48, 1).$rational(5760, 100);    // 27°48'57.6"
        $lng = $rational(50, 1).$rational(19, 1).$rational(3360, 100);    // 50°19'33.6"

        return 'II'.pack('vV', 42, $ifd0Offset).$ifd0.$make.$gps.$lat.$lng;
    }
}
