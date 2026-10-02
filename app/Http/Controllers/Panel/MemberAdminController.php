<?php

namespace App\Http\Controllers\Panel;

use App\Application\Members\UseCases\AdministerMembers;
use App\Domain\Members\Data\MemberSearch;
use App\Domain\Members\MemberAdministrationRefused;
use App\Domain\Members\MemberRole;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/** /painel/membros: filters live in the URL (?busca=&papel=&situacao=&ordem=&pagina=). */
class MemberAdminController extends Controller
{
    public function index(Request $request, AdministerMembers $members): Response
    {
        $search = MemberSearch::from($request->query('busca'), $request->query('papel'), $request->query('situacao'), $request->query('ordem'));

        return Inertia::render('Panel/Members/Index', [
            ...$members->list($search, (int) $request->query('pagina', 1)),
            'filters' => [
                'busca' => $search->query ?? '',
                'papel' => $search->role?->value,
                'situacao' => $search->blockedOnly ? 'bloqueados' : null,
                'ordem' => $search->sort,
            ],
        ]);
    }

    public function show(Request $request, AdministerMembers $members, int $member): Response
    {
        $data = $members->detail($member) ?? abort(404);
        $actor = $this->actor($request);

        return Inertia::render('Panel/Members/Show', [
            ...$data,
            'canManage' => $actor->role === MemberRole::Admin,
            'isSelf' => $actor->id === $member,
        ]);
    }

    public function role(Request $request, AdministerMembers $members, int $member): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::enum(MemberRole::class)]]);
        $actor = $this->actor($request);

        return $this->run(fn () => $members->changeRole($actor->id, $actor->role, $member, MemberRole::from($data['role'])), 'Papel atualizado.');
    }

    public function block(Request $request, AdministerMembers $members, int $member): RedirectResponse
    {
        $data = $request->validate(
            ['reason' => ['required', 'string', 'min:10', 'max:500']],
            ['reason.required' => 'Registre o motivo do bloqueio.', 'reason.min' => 'Escreva pelo menos 10 caracteres.'],
        );

        return $this->run(fn () => $members->block($this->actor($request)->id, $member, $data['reason']), 'Membro bloqueado.');
    }

    public function unblock(Request $request, AdministerMembers $members, int $member): RedirectResponse
    {
        return $this->run(fn () => $members->unblock($this->actor($request)->id, $member), 'Bloqueio retirado.');
    }

    public function destroy(Request $request, AdministerMembers $members, int $member): RedirectResponse
    {
        $actor = $this->actor($request);
        $this->run(fn () => $members->delete($actor->id, $actor->role, $member), '');

        return redirect()->route('panel.members')->with('toast', 'Conta excluída, com relatos e fotos.');
    }

    private function run(Closure $action, string $done): RedirectResponse
    {
        try {
            $action();
        } catch (MemberAdministrationRefused $e) {
            throw ValidationException::withMessages([$e->field => $e->getMessage()]);
        }

        return back()->with('toast', $done);
    }

    private function actor(Request $request): Member
    {
        /** @var Member $member */
        $member = $request->user();

        return $member;
    }
}
