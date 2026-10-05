<?php

namespace App\Http\Controllers\Origin;

use App\Application\Origin\UseCases\GetCachiDossier;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class CachiController extends Controller
{
    public function __invoke(GetCachiDossier $dossier, ContentSeo $seo): Response
    {
        $page = $dossier->execute();

        return Inertia::render('Origin/Cachi', [...$page, 'seo' => $seo->cachi($page['dossier'])->toArray()]);
    }
}
