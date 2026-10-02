<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/** /painel/conteudo: the way into each content screen. */
class ContentHubController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Panel/Content/Hub');
    }
}
