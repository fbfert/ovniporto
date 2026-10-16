<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\InactivityNotifier;
use App\Domain\Members\Contracts\MemberContentAnonymizer;
use App\Domain\Members\Contracts\MemberRepository;
use DateTimeImmutable;

/**
 * The daily inactivity cycle. A member without access for $years years minus $noticeDays gets
 * one notice; one still away at $years years, and noticed at least $noticeDays before, is
 * anonymized: published reports stay with the nickname, everything else that identifies the
 * person goes (MemberContentAnonymizer). Panel accounts are never anonymized: the admins get
 * one alert per cycle instead. Signing in restarts the cycle. Each step marks what it did
 * before moving on, so a run can stop anywhere and the next one does not repeat it.
 */
final readonly class AnonymizeInactiveMembers
{
    private const BATCH = 200;

    /** @param iterable<MemberContentAnonymizer> $anonymizers */
    public function __construct(
        private MemberRepository $members,
        private InactivityNotifier $notifier,
        private iterable $anonymizers,
    ) {}

    /** @return array{noticed: int, anonymized: int, alerted: int} */
    public function execute(DateTimeImmutable $now, int $years, int $noticeDays): array
    {
        $term = $now->modify("-{$years} years");

        return [
            'noticed' => $this->notice($now, $term->modify("+{$noticeDays} days"), $noticeDays),
            'anonymized' => $this->anonymize($now, $term, $now->modify("-{$noticeDays} days")),
            'alerted' => $this->alertStaff($now, $term),
        ];
    }

    private function notice(DateTimeImmutable $now, DateTimeImmutable $lastSeenBefore, int $noticeDays): int
    {
        $count = 0;
        while (($due = $this->members->dueForInactivityNotice($lastSeenBefore, self::BATCH)) !== []) {
            foreach ($due as $member) {
                $this->members->markInactivityNoticeSent($member['id'], $now);
                $this->notifier->noticeAnonymization($member['email'], $member['nickname'], $now->modify("+{$noticeDays} days"));
                $count++;
            }
        }

        return $count;
    }

    private function anonymize(DateTimeImmutable $now, DateTimeImmutable $lastSeenBefore, DateTimeImmutable $noticedBefore): int
    {
        $count = 0;
        while (($due = $this->members->dueForAnonymization($lastSeenBefore, $noticedBefore, self::BATCH)) !== []) {
            foreach ($due as $member) {
                foreach ($this->anonymizers as $anonymizer) {
                    $anonymizer->anonymizeFor($member['id'], $member['email']);
                }
                $this->members->anonymize($member['id'], $now);
                $count++;
            }
        }

        return $count;
    }

    private function alertStaff(DateTimeImmutable $now, DateTimeImmutable $lastSeenBefore): int
    {
        $count = 0;
        while (($due = $this->members->inactiveStaffToAlert($lastSeenBefore, self::BATCH)) !== []) {
            foreach ($due as $member) {
                $this->members->markInactivityAlerted($member['id'], $now);
                $this->notifier->alertInactiveStaff($member['id'], $member['nickname'], $member['role'], $member['lastSeenAt']);
                $count++;
            }
        }

        return $count;
    }
}
