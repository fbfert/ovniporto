<?php

namespace App\Http\Controllers;

use App\Application\Content\UseCases\GetHomeData;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(GetHomeData $getHomeData): Response
    {
        return Inertia::render('Home', $getHomeData->execute()->toArray());
    }
}
