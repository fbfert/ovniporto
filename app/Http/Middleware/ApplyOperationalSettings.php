<?php

namespace App\Http\Middleware;

use App\Infrastructure\Settings\OperationalSettingsApplier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The coordinates saved in /painel/coordenadas, in force for this request (see OperationalSettingsApplier). */
final readonly class ApplyOperationalSettings
{
    public function __construct(private OperationalSettingsApplier $applier) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->applier->refresh();

        return $next($request);
    }
}
