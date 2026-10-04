<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;
use App\Domain\Members\MemberRole;
use App\Domain\Members\Nickname;
use App\Models\Member;
use DateTimeInterface;

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
}
