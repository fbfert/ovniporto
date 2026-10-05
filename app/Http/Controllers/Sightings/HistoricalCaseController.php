<?php

namespace App\Http\Controllers\Sightings;

use App\Application\Sightings\UseCases\GetHistoricalCase;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class HistoricalCaseController extends Controller
{
    public function __invoke(GetHistoricalCase $getCase, ContentSeo $seo, string $slug): Response
    {
        $page = $getCase->execute($slug) ?? abort(404);

        return Inertia::render('Sightings/HistoricalCase', [...$page, 'seo' => $seo->historicalCase($page['case'])->toArray()]);
    }
}
