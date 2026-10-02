<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\GetCommunityRules;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function __invoke(GetCommunityRules $getRules): Response
    {
        return Inertia::render('Content/Community', ['rules' => $getRules->execute()]);
    }
}
