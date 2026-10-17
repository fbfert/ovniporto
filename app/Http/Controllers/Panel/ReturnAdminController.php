<?php

namespace App\Http\Controllers\Panel;

use App\Application\Orders\UseCases\OperateReturns;
use App\Domain\Orders\InvalidReturnTransition;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/** /painel/pedidos/devolucoes (store and admin): filters in the URL (?status=&busca=). */
class ReturnAdminController extends Controller
{
    public function index(Request $request, OperateReturns $returns): Response
    {
        $status = (string) $request->query('status', '');
        $search = mb_substr(trim((string) $request->query('busca', '')), 0, 80);

        return Inertia::render('Panel/Returns/Index', [
            ...$returns->list($search === '' ? null : $search, $status === '' ? null : $status),
            'filters' => ['status' => $status === '' ? null : $status, 'busca' => $search],
        ]);
    }

    public function show(OperateReturns $returns, string $protocol): Response
    {
        return Inertia::render('Panel/Returns/Show', ['request' => $returns->sheet($protocol) ?? abort(404)]);
    }

    public function status(Request $request, OperateReturns $returns, string $protocol): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:20'],
            'note' => ['required', 'string', 'max:500'],
        ], [
            'note.required' => 'Escreva uma nota: o que foi combinado ou feito.',
            'note.max' => 'Use até 500 caracteres.',
        ]);
        try {
            $returns->changeStatus((int) $request->user()?->getAuthIdentifier(), $protocol, $data['status'], $data['note']);
        } catch (InvalidReturnTransition|InvalidArgumentException $e) {
            throw ValidationException::withMessages(['note' => $e->getMessage()]);
        }

        return back()->with('toast', 'Status da devolução atualizado.');
    }
}
