<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\RenderContentBlocks;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class LegendController extends Controller
{
    public function __invoke(RenderContentBlocks $render): Response
    {
        return Inertia::render('Content/Legend', [
            'legendHtml' => $render->execute(['legend_body'])['legend_body'],
        ]);
    }
}
