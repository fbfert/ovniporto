<?php

namespace App\Http\Controllers\Panel;

use App\Application\Origin\UseCases\ManageCollaborators;
use App\Http\Controllers\Controller;
use App\Http\Responses\CsvDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** /painel/colaboradores: offers to help the origin research. */
class CollaboratorAdminController extends Controller
{
    public function index(ManageCollaborators $collaborators): Response
    {
        return Inertia::render('Panel/Content/Collaborators', $collaborators->page());
    }

    public function export(ManageCollaborators $collaborators): StreamedResponse
    {
        return CsvDownload::make('colaboradores-'.now()->format('Y-m-d').'.csv', $collaborators->export());
    }

    public function destroy(Request $request, ManageCollaborators $collaborators, int $collaborator): RedirectResponse
    {
        $collaborators->remove((int) $request->user()?->getAuthIdentifier(), $collaborator);

        return back()->with('toast', 'Colaborador removido.');
    }
}
