<?php

namespace App\Http\Controllers\Content;

use App\Application\Content\UseCases\GetFaqPage;
use App\Http\Controllers\Controller;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function __invoke(GetFaqPage $getFaq, ContentSeo $seo): Response
    {
        $faqs = $getFaq->execute();

        return Inertia::render('Content/Faq', ['faqs' => $faqs, 'seo' => $seo->faq($faqs)->toArray()]);
    }
}
