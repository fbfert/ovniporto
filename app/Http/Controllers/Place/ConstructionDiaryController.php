<?php

namespace App\Http\Controllers\Place;

use App\Application\Place\UseCases\GetConstructionPost;
use App\Application\Place\UseCases\ListConstructionPosts;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConstructionDiaryController extends Controller
{
    public function index(ListConstructionPosts $listPosts): Response
    {
        return Inertia::render('Place/Diary', ['posts' => $listPosts->execute()]);
    }

    public function show(GetConstructionPost $getPost, string $slug): Response
    {
        $post = $getPost->execute($slug) ?? abort(404);

        return Inertia::render('Place/DiaryPost', ['post' => $post]);
    }

    public function feed(ListConstructionPosts $listPosts): HttpResponse
    {
        return response()
            ->view('feeds.construction', ['posts' => $listPosts->execute()])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
