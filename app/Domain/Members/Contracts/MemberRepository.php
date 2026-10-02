<?php

namespace App\Domain\Members\Contracts;

use App\Domain\Members\Data\Identity;
use DateTimeInterface;

interface MemberRepository
{
    public function countActive(): int;

    public function findIdByProviderId(string $providerId): ?int;

    /** Creates a member with role "member" from the provider identity; returns its id. */
    public function createFromIdentity(Identity $identity): int;

    /** Keeps name, e-mail and avatar in sync with the provider on each sign-in. */
    public function refreshIdentity(int $memberId, Identity $identity): void;

    public function nicknameTaken(string $nickname, ?int $exceptMemberId = null): bool;

    public function completeProfile(int $memberId, string $nickname, ?string $city, DateTimeInterface $termsAcceptedAt): void;

    public function updateProfile(int $memberId, string $nickname, ?string $city): void;

    /** @return array{id: int, name: string, email: string, avatarUrl: ?string, nickname: ?string, city: ?string, role: string, termsAcceptedAt: ?string, createdAt: string}|null */
    public function find(int $memberId): ?array;

    public function delete(int $memberId): void;

    /** Blocked by the tower: no new reports (nor orders, with the store). */
    public function isBlocked(int $memberId): bool;
}
