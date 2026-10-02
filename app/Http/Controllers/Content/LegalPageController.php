<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\GetLegalPage;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    /** @param 'privacy'|'terms' $kind set by the route */
    public function __invoke(GetLegalPage $getLegalPage, string $kind): Response
    {
        return Inertia::render('Content/Legal', ['kind' => $kind, ...$getLegalPage->execute($kind)]);
    }
}
