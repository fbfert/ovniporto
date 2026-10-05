<?php

namespace App\Http\Controllers\Origin;

use App\Application\Origin\UseCases\GetRelato;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class RelatoController extends Controller
{
    public function __invoke(GetRelato $relato, ContentSeo $seo): Response
    {
        return Inertia::render('Origin/Relato', ['relatoHtml' => $relato->execute()['html'], 'seo' => $seo->relato()->toArray()]);
    }
}
