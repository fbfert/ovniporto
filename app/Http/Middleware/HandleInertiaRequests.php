<?php

namespace App\Http\Middleware;

use App\Application\Content\UseCases\GetCommunityLinks;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appUrl' => rtrim((string) config('app.url'), '/'),
            'currentUrl' => $request->url(),
            'community' => fn () => app(GetCommunityLinks::class)->execute(),
            'flash' => [
                'toast' => fn () => $request->session()->get('toast'),
            ],
        ];
    }
}
