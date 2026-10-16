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

    public function acceptTerms(int $memberId, DateTimeInterface $acceptedAt): void;

    /** @return array{id: int, name: ?string, email: ?string, avatarUrl: ?string, nickname: ?string, city: ?string, role: string, termsAcceptedAt: ?string, createdAt: string}|null name and e-mail are null once anonymized */
    public function find(int $memberId): ?array;

    public function delete(int $memberId): void;

    /** Blocked by the tower: no new reports (nor orders, with the store). */
    public function isBlocked(int $memberId): bool;

    /** Last access; also restarts the inactivity cycle (notice and staff alert cleared). */
    public function touchLastSeen(int $memberId, DateTimeInterface $at): void;

    /**
     * Members of any role, not anonymized, last seen at or before $lastSeenBefore and not yet noticed.
     *
     * @return list<array{id: int, email: string, nickname: ?string, lastSeenAt: string}>
     */
    public function dueForInactivityNotice(DateTimeInterface $lastSeenBefore, int $limit): array;

    public function markInactivityNoticeSent(int $memberId, DateTimeInterface $at): void;

    /**
     * Members without a panel role, last seen at or before $lastSeenBefore and noticed at or before $noticedBefore.
     *
     * @return list<array{id: int, email: string}>
     */
    public function dueForAnonymization(DateTimeInterface $lastSeenBefore, DateTimeInterface $noticedBefore, int $limit): array;

    /**
     * Members with a panel role, last seen at or before $lastSeenBefore, not yet alerted in this cycle.
     *
     * @return list<array{id: int, nickname: ?string, role: string, lastSeenAt: string}>
     */
    public function inactiveStaffToAlert(DateTimeInterface $lastSeenBefore, int $limit): array;

    public function markInactivityAlerted(int $memberId, DateTimeInterface $at): void;

    /** Name, e-mail, Google link, avatar and city go; the nickname stays and anonymized_at is set. */
    public function anonymize(int $memberId, DateTimeInterface $at): void;
}
