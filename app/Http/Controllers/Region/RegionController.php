<?php

namespace App\Http\Controllers\Region;

use App\Application\Region\UseCases\GetRegionPartner;
use App\Application\Region\UseCases\ListRegionPartners;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegionFilterRequest;
use App\Http\Seo\ContentSeo;
use Inertia\Inertia;
use Inertia\Response;

class RegionController extends Controller
{
    public function index(RegionFilterRequest $request, ListRegionPartners $listPartners): Response
    {
        return Inertia::render('Region/Index', [
            ...$listPartners->execute($request->type(), $request->search()),
            'filters' => ['tipo' => $request->type()?->value, 'q' => $request->search()],
        ]);
    }

    public function show(GetRegionPartner $getPartner, ContentSeo $seo, string $slug): Response
    {
        $page = $getPartner->execute($slug) ?? abort(404);

        return Inertia::render('Region/Show', [...$page, 'seo' => $seo->partner($page['partner'])->toArray()]);
    }
}
