<?php

namespace App\Http\Controllers\Origin;

use App\Application\Origin\UseCases\ApplyAsCollaborator;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyAsCollaboratorRequest;
use Illuminate\Http\RedirectResponse;

class CollaboratorController extends Controller
{
    /** A filled honeypot gets the same answer as a person, and nothing is stored. */
    public function store(ApplyAsCollaboratorRequest $request, ApplyAsCollaborator $apply): RedirectResponse
    {
        if (! $request->isBot()) {
            $apply->execute(
                $request->string('name')->toString(),
                $request->string('email')->toString(),
                $request->string('location')->toString(),
                $request->areas(),
                $request->string('message')->toString(),
            );
        }

        return back()->with('toast', 'Recebemos sua oferta. Mandamos um e-mail de confirmação.');
    }
}
