<?php

namespace App\Http\Middleware;

use App\Application\Content\UseCases\GetCommunityLinks;
use App\Domain\Panel\PanelArea;
use App\Models\Member;
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
            // Only what the header needs about the signed-in member: never the real name or e-mail.
            'auth' => fn () => ['member' => $this->member($request)],
            // Panel menu: the areas this role may open (the server checks each one again).
            'panelAreas' => fn () => $this->panelAreas($request),
            'flash' => [
                'toast' => fn () => $request->session()->get('toast'),
            ],
        ];
    }

    /** @return list<string>|null */
    private function panelAreas(Request $request): ?array
    {
        $member = $request->user();
        if (! $member instanceof Member || ! $request->routeIs('panel', 'panel.*')) {
            return null;
        }

        return array_map(fn (PanelArea $area) => $area->value, PanelArea::openTo($member->role));
    }

    /** @return array{nickname: ?string, avatarUrl: ?string, canOpenPanel: bool, complete: bool}|null */
    private function member(Request $request): ?array
    {
        $member = $request->user();
        if (! $member instanceof Member) {
            return null;
        }

        return [
            'nickname' => $member->nickname,
            'avatarUrl' => $member->avatar_url,
            'canOpenPanel' => $member->role->canOpenPanel(),
            'complete' => $member->hasCompleteProfile(),
        ];
    }
}
