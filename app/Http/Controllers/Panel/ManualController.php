<?php

namespace App\Http\Controllers\Panel;

use App\Application\Manual\UseCases\ListManualChapters;
use App\Application\Manual\UseCases\ShowManualChapter;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** /painel/manual: the operations manual, filtered by the reader's role. */
class ManualController extends Controller
{
    public function index(Request $request, ListManualChapters $chapters): Response
    {
        return Inertia::render('Panel/Manual/Index', ['chapters' => $chapters->execute($this->member($request)->role)]);
    }

    public function show(Request $request, ShowManualChapter $chapter, string $slug): Response
    {
        return Inertia::render('Panel/Manual/Chapter', $chapter->execute($this->member($request)->role, $slug) ?? abort(404));
    }

    private function member(Request $request): Member
    {
        $member = $request->user();
        abort_unless($member instanceof Member, 403);

        return $member;
    }
}
