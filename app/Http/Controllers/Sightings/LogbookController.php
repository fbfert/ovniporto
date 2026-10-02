<?php

namespace App\Http\Controllers\Sightings;

use App\Application\Sightings\UseCases\GetSightingPage;
use App\Application\Sightings\UseCases\ListPublicSightings;
use App\Domain\Sightings\Data\SightingFilters;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogbookController extends Controller
{
    /** /mapa: filters live in the URL (?periodo=&tipo=), pages are merged by "Carregar mais". */
    public function index(Request $request, ListPublicSightings $list): Response
    {
        $filters = SightingFilters::from($request->query('periodo'), $request->query('tipo'));
        $page = max(1, (int) $request->query('pagina', 1));
        ['cards' => $cards, 'hasMore' => $hasMore] = $list->page($filters, $page);

        return Inertia::render('Sightings/Logbook', [
            'filters' => ['periodo' => $filters->period, 'tipo' => $filters->type?->value],
            'total' => $list->total($filters),
            'pins' => fn () => $list->pins($filters),
            'cards' => Inertia::merge($cards),
            'page' => $page,
            'hasMore' => $hasMore,
        ]);
    }

    public function show(Request $request, GetSightingPage $getPage, int $sighting): Response
    {
        $viewer = $request->user()?->getAuthIdentifier();
        $page = $getPage->execute($sighting, $viewer === null ? null : (int) $viewer) ?? abort(404);

        return Inertia::render('Sightings/Show', $page);
    }
}
