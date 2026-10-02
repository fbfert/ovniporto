<?php

namespace App\Http\Controllers\Place;

use App\Application\Campaign\UseCases\GetSupportPage;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function __invoke(GetSupportPage $getSupportPage): Response
    {
        return Inertia::render('Place/Support', ['campaign' => $getSupportPage->execute()]);
    }
}
