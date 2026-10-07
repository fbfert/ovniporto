<?php

namespace App\Http\Middleware;

use App\Application\Content\UseCases\GetCommunityLinks;
use App\Application\Manual\UseCases\FindManualSection;
use App\Domain\Manual\InvalidManualData;
use App\Application\Orders\UseCases\ManageCart;
use App\Domain\Panel\PanelArea;
use App\Http\Seo\Seo;
use App\Http\Support\CartOwners;
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
            // Default sharing metadata by route name; content pages pass their own 'seo' prop.
            'seo' => fn () => Seo::forRoute($request->route()?->getName())->toArray(),
            'community' => fn () => app(GetCommunityLinks::class)->execute(),
            // Only what the header needs about the signed-in member: never the real name or e-mail.
            'auth' => fn () => ['member' => $this->member($request)],
            // Panel menu: the areas this role may open (the server checks each one again).
            'panelAreas' => fn () => $this->panelAreas($request),
            // "Como funciona": the manual section about the current panel screen (none on the manual itself).
            'manualLink' => fn () => $this->manualLink($request),
            // The drawer and the header counter, priced by the server on every visit.
            'cart' => fn () => $this->cart($request),
            'flash' => [
                'toast' => fn () => $request->session()->get('toast'),
                'cartOpen' => fn () => (bool) $request->session()->get('cartOpen'),
            ],
        ];
    }

    /** @return array{items: list<array<string, mixed>>, count: int, subtotalCents: int, weightGrams: int} */
    private function cart(Request $request): array
    {
        $owner = CartOwners::forRead($request);

        return $owner === null
            ? ['items' => [], 'count' => 0, 'subtotalCents' => 0, 'weightGrams' => 0]
            : app(ManageCart::class)->view($owner);
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

    private function manualLink(Request $request): ?string
    {
        $member = $request->user();
        $route = (string) $request->route()?->getName();
        if (! $member instanceof Member || ! $request->routeIs('panel', 'panel.*') || $request->routeIs('panel.manual*')) {
            return null;
        }

        try {
            return app(FindManualSection::class)->execute($member->role, $route);
        } catch (InvalidManualData $e) {
            // A broken chapter must not take the whole panel down: log it and drop the link.
            report($e);

            return null;
        }
    }

    /** @return array{nickname: ?string, avatarUrl: ?string, canOpenPanel: bool, complete: bool, blocked: bool}|null */
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
            'blocked' => $member->isBlocked(),
        ];
    }
}
