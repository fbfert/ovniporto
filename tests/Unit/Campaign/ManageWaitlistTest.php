<?php

use App\Application\Campaign\UseCases\ManageWaitlist;
use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Privacy\ConsentType;
use Tests\Support\SpyConsentLedger;

it('removes a subscription through the audit log and withdraws its consent', function () {
    $waitlist = Mockery::mock(WaitlistRepository::class);
    $waitlist->shouldReceive('remove')->once()->with(9)->andReturn('vigia@serra.com');
    $auditor = new class implements Auditor
    {
        /** @var list<AuditEntry> */
        public array $entries = [];

        public function audited(AuditEntry $entry, callable $snapshot, callable $change): mixed
        {
            $this->entries[] = $entry;

            return $change();
        }

        public function history(string $subjectType, int $subjectId): array
        {
            return [];
        }

        public function recent(int $page, int $perPage): array
        {
            return ['items' => [], 'hasMore' => false];
        }
    };
    $ledger = new SpyConsentLedger;

    (new ManageWaitlist($waitlist, $auditor, $ledger))->remove(3, 9);

    expect($auditor->entries[0]->action)->toBe('waitlist.removed')
        ->and(json_encode($auditor->entries[0]))->not->toContain('vigia@serra.com')
        ->and($ledger->withdrawals)->toBe([['type' => ConsentType::Newsletter, 'subject' => 'avise-me:9']]);
});
