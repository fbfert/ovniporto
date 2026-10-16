<?php

use App\Application\Privacy\UseCases\Unsubscribe;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Origin\Contracts\CollaboratorRepository;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\MailingList;
use Tests\Support\SpyConsentLedger;

it('shows the masked e-mail and removes nothing while previewing', function () {
    $waitlist = Mockery::mock(WaitlistRepository::class);
    $waitlist->shouldReceive('emailOf')->with(4)->andReturn('vigia@serra.com');
    $waitlist->shouldNotReceive('remove');
    $ledger = new SpyConsentLedger;

    $masked = (new Unsubscribe($waitlist, Mockery::mock(CollaboratorRepository::class), $ledger))->preview(MailingList::Waitlist, 4);

    expect($masked)->toBe('v***@serra.com')->and($ledger->withdrawals)->toBe([]);
});

it('has nothing to preview once the sign-up is gone', function () {
    $collaborators = Mockery::mock(CollaboratorRepository::class);
    $collaborators->shouldReceive('emailOf')->with(4)->andReturnNull();

    expect((new Unsubscribe(Mockery::mock(WaitlistRepository::class), $collaborators, new SpyConsentLedger))->preview(MailingList::Collaboration, 4))
        ->toBeNull();
});

it('removes the subscription and withdraws its consent', function () {
    $waitlist = Mockery::mock(WaitlistRepository::class);
    $waitlist->shouldReceive('remove')->once()->with(4)->andReturn('vigia@serra.com');
    $ledger = new SpyConsentLedger;

    (new Unsubscribe($waitlist, Mockery::mock(CollaboratorRepository::class), $ledger))->execute(MailingList::Waitlist, 4);

    expect($ledger->withdrawals)->toBe([['type' => ConsentType::Newsletter, 'subject' => 'avise-me:4']]);
});

it('treats a collaboration that no longer exists as done, not as an error', function () {
    $collaborators = Mockery::mock(CollaboratorRepository::class);
    $collaborators->shouldReceive('remove')->once()->with(8)->andReturnNull();
    $ledger = new SpyConsentLedger;

    (new Unsubscribe(Mockery::mock(WaitlistRepository::class), $collaborators, $ledger))->execute(MailingList::Collaboration, 8);

    expect($ledger->withdrawals)->toBe([['type' => ConsentType::Collaboration, 'subject' => 'colaborador:8']]);
});
