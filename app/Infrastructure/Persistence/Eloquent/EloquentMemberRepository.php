<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Content\UseCases\GetHomeData;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;
use App\Domain\Members\MemberRole;
use App\Domain\Members\Nickname;
use App\Infrastructure\Cache\FragmentCache;
use App\Models\Member;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

final class EloquentMemberRepository implements MemberRepository
{
    public function countActive(): int
    {
        return Member::query()->whereNotNull('terms_accepted_at')->count();
    }

    public function findIdByProviderId(string $providerId): ?int
    {
        $id = Member::query()->where('google_id', $providerId)->value('id');

        return $id === null ? null : (int) $id;
    }

    public function createFromIdentity(Identity $identity): int
    {
        return Member::query()->create([
            'google_id' => $identity->providerId,
            'name' => $identity->name,
            'email' => $identity->email,
            'avatar_url' => $identity->avatarUrl,
            'role' => MemberRole::Member,
        ])->id;
    }

    public function refreshIdentity(int $memberId, Identity $identity): void
    {
        Member::query()->whereKey($memberId)->update([
            'name' => $identity->name,
            'email' => $identity->email,
            'avatar_url' => $identity->avatarUrl,
        ]);
    }

    public function nicknameTaken(string $nickname, ?int $exceptMemberId = null): bool
    {
        return Member::query()
            ->whereRaw('lower(nickname) = ?', [Nickname::normalize($nickname)])
            ->when($exceptMemberId !== null, fn ($q) => $q->whereKeyNot($exceptMemberId))
            ->exists();
    }

    public function completeProfile(int $memberId, string $nickname, ?string $city, DateTimeInterface $termsAcceptedAt): void
    {
        Member::query()->whereKey($memberId)->update([
            'nickname' => Nickname::normalize($nickname),
            'city' => $city,
            'terms_accepted_at' => $termsAcceptedAt,
        ]);
        // A completed profile joins the home's member count (query updates skip model events).
        FragmentCache::bump(GetHomeData::FRAGMENT);
    }

    public function acceptTerms(int $memberId, DateTimeInterface $acceptedAt): void
    {
        Member::query()->whereKey($memberId)->update(['terms_accepted_at' => $acceptedAt]);
    }

    public function updateProfile(int $memberId, string $nickname, ?string $city): void
    {
        Member::query()->whereKey($memberId)->update(['nickname' => Nickname::normalize($nickname), 'city' => $city]);
    }

    public function find(int $memberId): ?array
    {
        $member = Member::query()->find($memberId);
        if ($member === null) {
            return null;
        }

        return [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'avatarUrl' => $member->avatar_url,
            'nickname' => $member->nickname,
            'city' => $member->city,
            'role' => $member->role->value,
            'termsAcceptedAt' => $member->terms_accepted_at?->toIso8601String(),
            'createdAt' => $member->created_at->toIso8601String(),
        ];
    }

    public function delete(int $memberId): void
    {
        Member::query()->whereKey($memberId)->delete();
    }

    public function isBlocked(int $memberId): bool
    {
        return Member::query()->whereKey($memberId)->whereNotNull('blocked_at')->exists();
    }

    public function touchLastSeen(int $memberId, DateTimeInterface $at): void
    {
        Member::query()->whereKey($memberId)->update(['last_seen_at' => $at, 'inactivity_notice_sent_at' => null, 'inactivity_alerted_at' => null]);
    }

    public function dueForInactivityNotice(DateTimeInterface $lastSeenBefore, int $limit): array
    {
        return $this->inactiveSince($lastSeenBefore)
            ->whereNull('inactivity_notice_sent_at')
            ->whereNotNull('email')
            ->limit($limit)
            ->get()
            ->map(fn (Member $m) => ['id' => $m->id, 'email' => (string) $m->email, 'nickname' => $m->nickname, 'lastSeenAt' => $m->last_seen_at?->toIso8601String() ?? ''])
            ->values()
            ->all();
    }

    public function markInactivityNoticeSent(int $memberId, DateTimeInterface $at): void
    {
        Member::query()->whereKey($memberId)->update(['inactivity_notice_sent_at' => $at]);
    }

    public function dueForAnonymization(DateTimeInterface $lastSeenBefore, DateTimeInterface $noticedBefore, int $limit): array
    {
        return $this->inactiveSince($lastSeenBefore)
            ->where('role', MemberRole::Member->value)
            ->whereNotNull('inactivity_notice_sent_at')
            ->where('inactivity_notice_sent_at', '<=', $noticedBefore)
            ->limit($limit)
            ->get()
            ->map(fn (Member $m) => ['id' => $m->id, 'email' => (string) $m->email])
            ->values()
            ->all();
    }

    public function inactiveStaffToAlert(DateTimeInterface $lastSeenBefore, int $limit): array
    {
        return $this->inactiveSince($lastSeenBefore)
            ->where('role', '!=', MemberRole::Member->value)
            ->whereNull('inactivity_alerted_at')
            ->limit($limit)
            ->get()
            ->map(fn (Member $m) => ['id' => $m->id, 'nickname' => $m->nickname, 'role' => $m->role->value, 'lastSeenAt' => $m->last_seen_at?->toIso8601String() ?? ''])
            ->values()
            ->all();
    }

    public function markInactivityAlerted(int $memberId, DateTimeInterface $at): void
    {
        Member::query()->whereKey($memberId)->update(['inactivity_alerted_at' => $at]);
    }

    public function anonymize(int $memberId, DateTimeInterface $at): void
    {
        Member::query()->whereKey($memberId)->whereNull('anonymized_at')->update([
            'google_id' => null,
            'name' => null,
            'email' => null,
            'avatar_url' => null,
            'city' => null,
            // Ends any remembered session left on the site or the app.
            'remember_token' => null,
            'anonymized_at' => $at,
        ]);
    }

    /** @return Builder<Member> members still identified whose last access is at or before $cutoff */
    private function inactiveSince(DateTimeInterface $cutoff): Builder
    {
        return Member::query()->whereNull('anonymized_at')->whereNotNull('last_seen_at')->where('last_seen_at', '<=', $cutoff)->orderBy('id');
    }
}
