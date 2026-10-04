<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\GetOgImage;
use App\Domain\Content\Sharing\OgKind;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OgImageController extends Controller
{
    /** The version is in the URL, so each image is immutable; an old version redirects to the current one. */
    public function __invoke(GetOgImage $getImage, string $kind, string $key, string $version): BinaryFileResponse|RedirectResponse
    {
        $image = $getImage->execute(OgKind::from($kind), $key, $version) ?? abort(404);

        if ($image['path'] === null) {
            return redirect()->route('og.image', ['kind' => $kind, 'key' => $key, 'version' => $image['version']]);
        }

        return response()->file($image['path'], [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
