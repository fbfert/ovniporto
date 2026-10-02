<?php

namespace App\Http\Controllers\Place;

use App\Application\Place\UseCases\GetPlacePage;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PlaceController extends Controller
{
    public function __invoke(GetPlacePage $getPlacePage): Response
    {
        return Inertia::render('Place/Place', $getPlacePage->execute());
    }
}
