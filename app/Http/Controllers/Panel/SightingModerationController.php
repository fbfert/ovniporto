<?php

namespace App\Http\Controllers\Panel;

use App\Application\Sightings\UseCases\ModerateSighting;
use App\Application\Sightings\UseCases\ReviewSightings;
use App\Domain\Sightings\InvalidModeration;
use App\Domain\Sightings\SightingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\RejectSightingRequest;
use App\Http\Requests\Panel\RequestChangesRequest;
use App\Http\Requests\Panel\UnpublishSightingRequest;
use Closure;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/** /painel/relatos: the queue in tabs and the review screen with the four decisions. */
class SightingModerationController extends Controller
{
    /** URL tab → status; the first one is the default. */
    public const TABS = [
        'pendentes' => SightingStatus::Pending,
        'ajuste' => SightingStatus::ChangesRequested,
        'aprovados' => SightingStatus::Approved,
        'rejeitados' => SightingStatus::Rejected,
    ];

    public function index(Request $request, ReviewSightings $review): Response
    {
        $tab = array_key_exists((string) $request->query('aba'), self::TABS) ? (string) $request->query('aba') : 'pendentes';
        $queue = $review->queue(self::TABS[$tab], (int) $request->query('pagina', 1), new DateTimeImmutable);

        return Inertia::render('Panel/Sightings/Queue', [
            'tab' => $tab,
            'counts' => collect(self::TABS)->map(fn (SightingStatus $s) => $queue['counts'][$s->value] ?? 0)->all(),
            'items' => $queue['items'],
            'page' => $queue['page'],
            'hasMore' => $queue['hasMore'],
        ]);
    }

    public function show(ReviewSightings $review, int $sighting): Response
    {
        $data = $review->review($sighting, new DateTimeImmutable) ?? abort(404);
        $status = SightingStatus::from($data['sighting']['status']);

        return Inertia::render('Panel/Sightings/Review', [
            ...$data,
            'tab' => array_search($status, self::TABS, true),
        ]);
    }

    public function approve(Request $request, ModerateSighting $moderate, ReviewSightings $review, int $sighting): RedirectResponse
    {
        return $this->decide($request, $review, $sighting, 'Relato aprovado e publicado no Livro.', fn (int $by) => $moderate->approve($by, $sighting));
    }

    public function requestChanges(RequestChangesRequest $request, ModerateSighting $moderate, ReviewSightings $review, int $sighting): RedirectResponse
    {
        return $this->decide($request, $review, $sighting, 'Pedido de ajuste enviado ao autor.', fn (int $by) => $moderate->requestChanges($by, $sighting, $request->string('message')->toString()));
    }

    public function reject(RejectSightingRequest $request, ModerateSighting $moderate, ReviewSightings $review, int $sighting): RedirectResponse
    {
        return $this->decide($request, $review, $sighting, 'Relato rejeitado. O autor foi avisado.', fn (int $by) => $moderate->reject($by, $sighting, $request->reason(), $request->input('detail')));
    }

    public function unpublish(UnpublishSightingRequest $request, ModerateSighting $moderate, ReviewSightings $review, int $sighting): RedirectResponse
    {
        return $this->decide($request, $review, $sighting, 'Relato despublicado: voltou para a fila.', fn (int $by) => $moderate->unpublish($by, $sighting, $request->string('note')->toString()));
    }

    /**
     * Runs the decision and moves on to the next report of the same tab, so a
     * moderator can go through the queue without coming back to the list.
     *
     * @param  Closure(int): void  $action
     */
    private function decide(Request $request, ReviewSightings $review, int $sighting, string $done, Closure $action): RedirectResponse
    {
        $next = $review->nextAfter($sighting);
        try {
            $action((int) $request->user()?->getAuthIdentifier());
        } catch (InvalidModeration $e) {
            throw ValidationException::withMessages([$e->field => $e->getMessage()]);
        }

        $to = $next !== null ? route('panel.sightings.show', $next) : route('panel.sightings');

        return redirect($to)->with('toast', $done);
    }
}
