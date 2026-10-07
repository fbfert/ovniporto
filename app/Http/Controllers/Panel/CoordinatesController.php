<?php

namespace App\Http\Controllers\Panel;

use App\Application\Settings\UseCases\SaveCoordinates;
use App\Application\Settings\UseCases\ShowCoordinates;
use App\Application\Settings\UseCases\TestCoordinates;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\SaveCoordinatesRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** /painel/coordenadas (admin only): e-mail, alerts and shipping settings, plus two test buttons. */
class CoordinatesController extends Controller
{
    public function show(Request $request, ShowCoordinates $coordinates): Response
    {
        return Inertia::render('Panel/Coordinates/Index', [
            ...$coordinates->execute(),
            // The answer of the last test button, once, right after it was pressed.
            'test' => $request->session()->get('coordinatesTest'),
        ]);
    }

    public function update(SaveCoordinatesRequest $request, SaveCoordinates $save): RedirectResponse
    {
        /** @var array<string, string|null> $fields */
        $fields = array_map(fn ($v) => $v === null ? null : (string) $v, $request->validated());
        $save->execute($this->actorId($request), $request->group(), $fields);

        return back()->with('toast', 'Coordenadas salvas. Já valem a partir de agora.');
    }

    public function removePassword(Request $request, SaveCoordinates $save): RedirectResponse
    {
        $save->removePassword($this->actorId($request));

        return back()->with('toast', 'Senha do SMTP removida.');
    }

    public function testMail(Request $request, TestCoordinates $test): RedirectResponse
    {
        /** @var Member $member */
        $member = $request->user();
        $result = $test->mail($member->id, (string) $member->email);

        return back()->with('coordinatesTest', ['kind' => 'mail', 'to' => $member->email, ...$result->toArray()]);
    }

    public function testAlert(Request $request, TestCoordinates $test): RedirectResponse
    {
        $result = $test->alert($this->actorId($request));

        return back()->with('coordinatesTest', ['kind' => 'alert', 'to' => null, ...$result->toArray()]);
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
