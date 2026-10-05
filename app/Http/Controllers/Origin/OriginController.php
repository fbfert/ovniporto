<?php

namespace App\Http\Controllers\Origin;

use App\Application\Origin\UseCases\GetOriginHub;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class OriginController extends Controller
{
    public function __invoke(GetOriginHub $hub): Response
    {
        return Inertia::render('Origin/Hub', $hub->execute());
    }
}
