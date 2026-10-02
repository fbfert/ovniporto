<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/** Panel areas whose tools arrive with later changes (store, members, content). */
class PanelUpcomingController extends Controller
{
    public function __invoke(string $area): Response
    {
        return Inertia::render('Panel/Upcoming', ['area' => $area]);
    }
}
