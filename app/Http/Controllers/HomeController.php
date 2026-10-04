<?php

namespace App\Http\Controllers;

use App\Application\Content\UseCases\GetHomeData;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(GetHomeData $getHomeData, ContentSeo $seo): Response
    {
        return Inertia::render('Home', [...$getHomeData->execute()->toArray(), 'seo' => $seo->home()->toArray()]);
    }
}
