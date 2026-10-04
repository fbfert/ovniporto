<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Seo\Seo;
use Inertia\Inertia;
use Inertia\Response;

class PostcardController extends Controller
{
    /** The image is a static file written by `php artisan brand:postal`. */
    public function __invoke(): Response
    {
        return Inertia::render('Postcard/Show', [
            'shareUrl' => Seo::appUrl().'/postal',
            'imageUrl' => '/brand/postal.jpg',
        ]);
    }
}
