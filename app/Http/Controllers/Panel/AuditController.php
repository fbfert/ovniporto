<?php

namespace App\Http\Controllers\Panel;

use App\Domain\Audit\Contracts\Auditor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditController extends Controller
{
    public const PER_PAGE = 50;

    public function __invoke(Request $request, Auditor $auditor): Response
    {
        $page = max(1, (int) $request->query('pagina', 1));

        return Inertia::render('Panel/Audit', [...$auditor->recent($page, self::PER_PAGE), 'page' => $page]);
    }
}
