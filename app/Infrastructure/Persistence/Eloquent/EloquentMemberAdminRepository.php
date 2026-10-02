<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Members\Contracts\MemberAdminRepository;
use App\Domain\Members\Data\MemberSearch;
use App\Domain\Members\MemberRole;
use App\Domain\Sightings\SightingStatus;
use App\Models\Member;
use App\Models\Sighting;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

final class EloquentMemberAdminRepository implements MemberAdminRepository
{
    public function search(MemberSearch $search, int $page, int $perPage): array
    {
        $query = Member::query()
            ->when($search->query !== null, function (Builder $q) use ($search) {
                $like = '%'.addcslashes((string) $search->query, '%_\\').'%';
                $q->where(fn (Builder $w) => $w->where('nickname', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('email', 'like', $like));
            })
            ->when($search->role !== null, fn (Builder $q) => $q->where('role', $search->role))
            ->when($search->blockedOnly, fn (Builder $q) => $q->whereNotNull('blocked_at'));

        $total = (clone $query)->count();
        $ordered = $search->sort === 'apelido'
            ? $query->orderByRaw('nickname is null')->orderBy('nickname')->orderBy('id')
            : $query->latest('id');

        return [
            'items' => $ordered
                ->withCount('sightings')
                ->forPage(max(1, $page), $perPage)
                ->get()
                ->map(fn (Member $m) => [
                    'id' => $m->id,
                    'nickname' => $m->nickname,
                    'name' => $m->name,
                    'email' => $m->email,
                    'avatarUrl' => $m->avatar_url,
                    'role' => $m->role->value,
                    'blocked' => $m->isBlocked(),
                    'reports' => (int) $m->getAttribute('sightings_count'),
                    'joinedAt' => $m->created_at->toDateString(),
                ])
                ->values()
                ->all(),
            'total' => $total,
        ];
    }

    public function detail(int $memberId): ?array
    {
        $member = Member::query()->find($memberId);
        if ($member === null) {
            return null;
        }

        return [
            'id' => $member->id,
            'nickname' => $member->nickname,
            'name' => $member->name,
            'email' => $member->email,
            'avatarUrl' => $member->avatar_url,
            'city' => $member->city,
            'role' => $member->role->value,
            'joinedAt' => $member->created_at->toDateString(),
            'termsAcceptedAt' => $member->terms_accepted_at?->toIso8601String(),
            'blockedAt' => $member->blocked_at?->toIso8601String(),
            'blockedReason' => $member->blocked_reason,
            'approved' => Sighting::query()->where('member_id', $member->id)->where('status', SightingStatus::Approved)->count(),
            'sightings' => Sighting::query()
                ->where('member_id', $member->id)
                ->latest('id')
                ->limit(50)
                ->get()
                ->map(fn (Sighting $s) => [
                    'id' => $s->id,
                    'type' => $s->type->value,
                    'status' => $s->status->value,
                    'observedDate' => $s->observed_date->toDateString(),
                ])
                ->values()
                ->all(),
        ];
    }

    public function roleOf(int $memberId): ?MemberRole
    {
        return Member::query()->find($memberId, ['id', 'role'])?->role;
    }

    public function snapshot(int $memberId): ?array
    {
        $member = Member::query()->find($memberId);

        return $member === null ? null : [
            'role' => $member->role->value,
            'blockedAt' => $member->blocked_at?->toIso8601String(),
            'blockedReason' => $member->blocked_reason,
        ];
    }

    public function setRole(int $memberId, MemberRole $role): void
    {
        Member::query()->whereKey($memberId)->update(['role' => $role]);
    }

    public function block(int $memberId, string $reason, DateTimeInterface $at): void
    {
        Member::query()->whereKey($memberId)->update(['blocked_at' => $at, 'blocked_reason' => $reason]);
    }

    public function unblock(int $memberId): void
    {
        Member::query()->whereKey($memberId)->update(['blocked_at' => null, 'blocked_reason' => null]);
    }
}
