<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class StyleguideController extends Controller
{
    public function __invoke(): Response
    {
        abort_unless(app()->isLocal(), 404);

        return Inertia::render('Dev/Styleguide');
    }
}
