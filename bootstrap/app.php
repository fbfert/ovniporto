<?php

use App\Http\Middleware\EnsureNotBlocked;
use App\Http\Middleware\EnsurePanelArea;
use App\Http\Middleware\EnsureProfileCompleted;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        $middleware->alias(['profile.complete' => EnsureProfileCompleted::class, 'panel' => EnsurePanelArea::class, 'not.blocked' => EnsureNotBlocked::class]);
        // Signed-in members who still visit /entrar go straight to their account.
        $middleware->redirectUsersTo(fn () => route('account'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Themed error pages. 500s keep Laravel's debug screen while APP_DEBUG is on.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();
            $themed = in_array($status, [403, 404, 419, 429, 500, 503], true);
            $showDebug = $status >= 500 && config('app.debug');

            if (! $themed || $showDebug || $request->expectsJson()) {
                return $response;
            }

            return Inertia::render('Errors/Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
