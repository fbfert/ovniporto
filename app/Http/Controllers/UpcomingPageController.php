<?php

namespace App\Http\Controllers;

use App\Support\UpcomingPages;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UpcomingPageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $slug = trim($request->path(), '/');
        $page = UpcomingPages::PAGES[$slug] ?? abort(404);

        return Inertia::render('ComingSoon', ['slug' => $slug, ...$page]);
    }
}
