<?php

namespace App\Http\Controllers\Members;

use App\Application\Members\UseCases\DeleteAccount;
use App\Application\Members\UseCases\GetAccountPage;
use App\Application\Members\UseCases\RequestDataExport;
use App\Application\Members\UseCases\UpdateProfile;
use App\Domain\Members\NicknameUnavailable;
use App\Domain\Sightings\Contracts\MemberSightingRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Sighting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    private const TABS = ['relatos', 'pedidos', 'dados', 'privacidade'];

    public function show(Request $request, GetAccountPage $getAccountPage): Response
    {
        $page = $getAccountPage->execute($this->memberId($request)) ?? abort(404);
        $tab = in_array($request->query('aba'), self::TABS, true) ? $request->query('aba') : 'relatos';

        return Inertia::render('Members/Account', [...$page, 'tab' => $tab]);
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile): RedirectResponse
    {
        try {
            $updateProfile->execute($this->memberId($request), $request->string('nickname')->toString(), $request->input('city'));
        } catch (NicknameUnavailable $e) {
            throw ValidationException::withMessages(['nickname' => CompleteProfileRequest::nicknameMessage($e->reason)]);
        }

        return back()->with('toast', 'Dados salvos.');
    }

    public function destroySighting(Request $request, Sighting $sighting, MemberSightingRepository $sightings): RedirectResponse
    {
        Gate::authorize('delete', $sighting);
        $sightings->deleteOwned($this->memberId($request), $sighting->id);

        return back()->with('toast', 'Relato excluído.');
    }

    public function export(Request $request, RequestDataExport $requestExport): RedirectResponse
    {
        $requestExport->execute($this->memberId($request));

        return back()->with('toast', 'Pedido recebido: o arquivo chega no seu e-mail em alguns minutos.');
    }

    public function destroy(Request $request, DeleteAccount $deleteAccount): RedirectResponse
    {
        $request->validate(['confirmation' => ['required', 'string']], ['confirmation.required' => 'Digite o seu apelido para confirmar.']);

        if (! $deleteAccount->execute($this->memberId($request), $request->string('confirmation')->toString())) {
            throw ValidationException::withMessages(['confirmation' => 'O apelido não confere. Nada foi excluído.']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('toast', 'Conta excluída. Guardei um lugar pra você, se quiser voltar.');
    }

    private function memberId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
