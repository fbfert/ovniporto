<?php

namespace Tests\Support;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;
use DateTimeImmutable;
use DateTimeInterface;

/** Members in memory, with the sign-in and inactivity rules of the Eloquent repository. */
final class InMemoryMembers implements MemberRepository
{
    /** @var array<int, array{providerId: ?string, name: ?string, email: ?string, nickname: ?string, role: string, lastSeenAt: ?DateTimeInterface, noticeAt: ?DateTimeInterface, alertedAt: ?DateTimeInterface, anonymizedAt: ?DateTimeInterface}> */
    public array $rows = [];

    /** Adds a member with a nickname, last seen at $lastSeenAt; returns its id. */
    public function add(string $email, DateTimeInterface $lastSeenAt, string $role = 'member', ?DateTimeInterface $noticeAt = null): int
    {
        $id = count($this->rows) + 1;
        $this->rows[$id] = [
            'providerId' => "google-{$id}",
            'name' => "Pessoa {$id}",
            'email' => $email,
            'nickname' => "apelido{$id}",
            'role' => $role,
            'lastSeenAt' => $lastSeenAt,
            'noticeAt' => $noticeAt,
            'alertedAt' => null,
            'anonymizedAt' => null,
        ];

        return $id;
    }

    public function countActive(): int
    {
        return count($this->rows);
    }

    public function findIdByProviderId(string $providerId): ?int
    {
        foreach ($this->rows as $id => $row) {
            if ($row['providerId'] === $providerId) {
                return $id;
            }
        }

        return null;
    }

    public function createFromIdentity(Identity $identity): int
    {
        $id = count($this->rows) + 1;
        $this->rows[$id] = [
            'providerId' => $identity->providerId,
            'name' => $identity->name,
            'email' => $identity->email,
            'nickname' => null,
            'role' => 'member',
            'lastSeenAt' => null,
            'noticeAt' => null,
            'alertedAt' => null,
            'anonymizedAt' => null,
        ];

        return $id;
    }

    public function refreshIdentity(int $memberId, Identity $identity): void
    {
        $this->rows[$memberId]['name'] = $identity->name;
        $this->rows[$memberId]['email'] = $identity->email;
    }

    public function nicknameTaken(string $nickname, ?int $exceptMemberId = null): bool
    {
        return false;
    }

    public function completeProfile(int $memberId, string $nickname, ?string $city, DateTimeInterface $termsAcceptedAt): void {}

    public function updateProfile(int $memberId, string $nickname, ?string $city): void {}

    public function acceptTerms(int $memberId, DateTimeInterface $acceptedAt): void {}

    public function find(int $memberId): ?array
    {
        return null;
    }

    public function delete(int $memberId): void
    {
        unset($this->rows[$memberId]);
    }

    public function isBlocked(int $memberId): bool
    {
        return false;
    }

    public function touchLastSeen(int $memberId, DateTimeInterface $at): void
    {
        $this->rows[$memberId]['lastSeenAt'] = $at;
        $this->rows[$memberId]['noticeAt'] = null;
        $this->rows[$memberId]['alertedAt'] = null;
    }

    public function dueForInactivityNotice(DateTimeInterface $lastSeenBefore, int $limit): array
    {
        $due = array_filter(
            $this->inactiveSince($lastSeenBefore),
            fn (array $row) => $row['noticeAt'] === null && $row['email'] !== null,
        );

        return array_slice(array_values(array_map(fn (int $id) => [
            'id' => $id,
            'email' => (string) $this->rows[$id]['email'],
            'nickname' => $this->rows[$id]['nickname'],
            'lastSeenAt' => self::iso($this->rows[$id]['lastSeenAt']),
        ], array_keys($due))), 0, $limit);
    }

    public function markInactivityNoticeSent(int $memberId, DateTimeInterface $at): void
    {
        $this->rows[$memberId]['noticeAt'] = $at;
    }

    public function dueForAnonymization(DateTimeInterface $lastSeenBefore, DateTimeInterface $noticedBefore, int $limit): array
    {
        $due = array_filter(
            $this->inactiveSince($lastSeenBefore),
            fn (array $row) => $row['role'] === 'member' && $row['noticeAt'] !== null && $row['noticeAt'] <= $noticedBefore,
        );

        return array_slice(array_values(array_map(
            fn (int $id) => ['id' => $id, 'email' => (string) $this->rows[$id]['email']],
            array_keys($due),
        )), 0, $limit);
    }

    public function inactiveStaffToAlert(DateTimeInterface $lastSeenBefore, int $limit): array
    {
        $due = array_filter(
            $this->inactiveSince($lastSeenBefore),
            fn (array $row) => $row['role'] !== 'member' && $row['alertedAt'] === null,
        );

        return array_slice(array_values(array_map(fn (int $id) => [
            'id' => $id,
            'nickname' => $this->rows[$id]['nickname'],
            'role' => $this->rows[$id]['role'],
            'lastSeenAt' => self::iso($this->rows[$id]['lastSeenAt']),
        ], array_keys($due))), 0, $limit);
    }

    public function markInactivityAlerted(int $memberId, DateTimeInterface $at): void
    {
        $this->rows[$memberId]['alertedAt'] = $at;
    }

    public function anonymize(int $memberId, DateTimeInterface $at): void
    {
        if ($this->rows[$memberId]['anonymizedAt'] !== null) {
            return;
        }
        $this->rows[$memberId] = [...$this->rows[$memberId], 'providerId' => null, 'name' => null, 'email' => null, 'anonymizedAt' => $at];
    }

    /** @return array<int, array{providerId: ?string, name: ?string, email: ?string, nickname: ?string, role: string, lastSeenAt: ?DateTimeInterface, noticeAt: ?DateTimeInterface, alertedAt: ?DateTimeInterface, anonymizedAt: ?DateTimeInterface}> */
    private function inactiveSince(DateTimeInterface $cutoff): array
    {
        return array_filter(
            $this->rows,
            fn (array $row) => $row['anonymizedAt'] === null && $row['lastSeenAt'] !== null && $row['lastSeenAt'] <= $cutoff,
        );
    }

    private static function iso(?DateTimeInterface $at): string
    {
        return $at === null ? '' : DateTimeImmutable::createFromInterface($at)->format(DATE_ATOM);
    }
}
