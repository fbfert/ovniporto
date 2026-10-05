<?php

namespace App\Http\Controllers\Origin;

use App\Application\Origin\UseCases\GetAtlas;
use App\Application\Origin\UseCases\GetAtlasCase;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class AtlasController extends Controller
{
    public function index(GetAtlas $atlas): Response
    {
        return Inertia::render('Origin/Atlas', $atlas->execute());
    }

    public function show(GetAtlasCase $getCase, ContentSeo $seo, string $slug): Response
    {
        $page = $getCase->execute($slug) ?? abort(404);

        return Inertia::render('Origin/AtlasCase', [...$page, 'seo' => $seo->atlasCase($page['case'])->toArray()]);
    }
}
