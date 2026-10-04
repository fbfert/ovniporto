<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Browsers report CSP violations here (report-only on staging): one log line each, nothing stored. */
class CspReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var array<string, mixed> $report */
        $report = (array) ($request->json('csp-report') ?? $request->json()->all());
        Log::warning('CSP violation', [
            'directive' => $report['violated-directive'] ?? $report['effective-directive'] ?? null,
            'blocked' => Str::limit((string) ($report['blocked-uri'] ?? ''), 200),
            'page' => Str::limit((string) ($report['document-uri'] ?? ''), 200),
        ]);

        return response()->noContent();
    }
}
