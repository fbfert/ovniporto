<?php

namespace App\Http\Controllers\Panel;

use App\Application\Panel\UseCases\GetPanelHome;
use App\Domain\Panel\PanelArea;
use App\Http\Controllers\Controller;
use App\Models\Member;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanelHomeController extends Controller
{
    public function __invoke(Request $request, GetPanelHome $home): Response
    {
        /** @var Member $member */
        $member = $request->user();

        return Inertia::render('Panel/Home', $home->execute(PanelArea::openTo($member->role), new DateTimeImmutable));
    }
}
