<?php

namespace App\Http\Controllers\Sightings;

use App\Domain\Panel\PanelArea;
use App\Domain\Sightings\SightingPhotoFiles;
use App\Domain\Sightings\SightingStatus;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\SightingPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Report photos live on the private disk. An approved report's photo is served
 * to anyone; a pending one only through a short signed URL and only to its
 * author or a moderator. Everything else is a 404: no hint that it exists.
 */
class SightingPhotoController extends Controller
{
    public function __invoke(Request $request, SightingPhoto $photo, int $width, string $format = 'webp'): Response
    {
        $sighting = $photo->sighting;
        // The 20 px placeholder and the AVIF siblings exist only for photos processed with them.
        $widths = $photo->avif ? [...($photo->variants ?? []), SightingPhotoFiles::PLACEHOLDER_WIDTH] : ($photo->variants ?? []);
        abort_unless(in_array($width, $widths, true) && ($format === 'webp' || $photo->avif), 404);

        $public = $sighting->status === SightingStatus::Approved && $sighting->published_at !== null;
        if (! $public) {
            $member = $request->user();
            $allowed = $request->hasValidSignature()
                && $member instanceof Member
                && ($sighting->member_id === $member->id || PanelArea::Sightings->allows($member->role));
            abort_unless($allowed, 404);
        }

        $path = "{$photo->path}-{$width}.{$format}";
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response((string) Storage::disk('local')->get($path), 200, [
            'Content-Type' => "image/{$format}",
            'Cache-Control' => $public ? 'public, max-age=604800, immutable' : 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
