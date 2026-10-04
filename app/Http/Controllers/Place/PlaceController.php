<?php

namespace App\Http\Controllers\Place;

use App\Application\Place\UseCases\GetPlacePage;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class PlaceController extends Controller
{
    public function __invoke(GetPlacePage $getPlacePage, ContentSeo $seo): Response
    {
        return Inertia::render('Place/Place', [...$getPlacePage->execute(), 'seo' => $seo->place()->toArray()]);
    }
}
