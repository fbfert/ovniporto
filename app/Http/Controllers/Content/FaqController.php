<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\GetFaqPage;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __invoke(GetFaqPage $getFaq): Response
    {
        return Inertia::render('Content/Faq', ['faqs' => $getFaq->execute()]);
    }
}
