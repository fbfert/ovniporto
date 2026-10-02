<?php

namespace App\Http\Controllers\Api;

use App\Application\Sightings\UseCases\ListPublicSightings;
use App\Domain\Sightings\Data\SightingFilters;
use App\Http\Controllers\Controller;
use App\Http\Resources\SightingPinResource;
use App\Infrastructure\Sightings\PublicSightingsCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SightingsApiController extends Controller
{
    /** GET /api/sightings?periodo=&tipo= — approved reports only, 7 public fields, cached 60 s. */
    public function __invoke(Request $request, ListPublicSightings $list): JsonResponse
    {
        $filters = SightingFilters::from($request->query('periodo'), $request->query('tipo'));

        return SightingPinResource::collection($list->pins($filters))
            ->response()
            ->header('Cache-Control', 'public, max-age='.PublicSightingsCache::TTL_SECONDS);
    }
}
