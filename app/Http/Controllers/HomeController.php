<?php

namespace App\Http\Controllers;

use App\Application\Content\UseCases\GetHomeData;
use App\Application\Origin\UseCases\GetCachiCover;
use App\Application\Origin\UseCases\GetRelato;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(GetHomeData $getHomeData, GetRelato $relato, GetCachiCover $cachiCover, ContentSeo $seo): Response
    {
        return Inertia::render('Home', [
            ...$getHomeData->execute(),
            'relatoOpening' => $relato->execute()['opening'],
            'cachiCover' => $cachiCover->execute(),
            'seo' => $seo->home()->toArray(),
        ]);
    }
}
