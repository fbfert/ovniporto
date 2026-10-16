<?php

namespace App\Http\Middleware;

use App\Domain\Members\Contracts\MemberRepository;
use App\Models\Member;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A session kept open from one day to the next still counts as access: the last access is
 * written at most once a day, and the day already written lives in the session, so an ordinary
 * request does not touch the database.
 */
class TouchLastSeen
{
    public const SESSION_KEY = 'last_seen_day';

    public function __construct(private readonly MemberRepository $members) {}

    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user();
        if ($member instanceof Member && $request->hasSession()) {
            $today = now()->toDateString();
            if ($request->session()->get(self::SESSION_KEY) !== $today) {
                $this->members->touchLastSeen($member->id, now());
                $request->session()->put(self::SESSION_KEY, $today);
            }
        }

        return $next($request);
    }
}
