<?php

use App\Application\Campaign\UseCases\SubscribeToWaitlist;
use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Domain\Campaign\Contracts\WaitlistRepository;
use App\Domain\Privacy\ConsentType;
use Tests\Support\SpyConsentLedger;

function fakeWaitlist(): WaitlistRepository
{
    return new class implements WaitlistRepository
    {
        /** @var array<string, int> */
        public array $rows = [];

        public function addIfAbsent(string $email, string $source, string $consentText): ?int
        {
            if (isset($this->rows[$email])) {
                return null;
            }

            return $this->rows[$email] = count($this->rows) + 1;
        }

        public function confirm(int $id): bool
        {
            return in_array($id, $this->rows, true);
        }

        public function subscribers(): array
        {
            return [];
        }

        public function remove(int $id): ?string
        {
            return null;
        }
    };
}

function spyNotifier(): WaitlistNotifier
{
    return new class implements WaitlistNotifier
    {
        /** @var list<string> */
        public array $sent = [];

        public function sendConfirmation(int $subscriptionId, string $email): void
        {
            $this->sent[] = $email;
        }
    };
}

it('normalizes the e-mail and sends one confirmation', function () {
    $waitlist = fakeWaitlist();
    $notifier = spyNotifier();

    (new SubscribeToWaitlist($waitlist, $notifier, new SpyConsentLedger))->execute('  Vigia@Serra.com ', 'home');

    expect($waitlist->rows)->toHaveKey('vigia@serra.com')
        ->and($notifier->sent)->toBe(['vigia@serra.com']);
});

it('is idempotent: a second subscription neither duplicates nor re-sends', function () {
    $waitlist = fakeWaitlist();
    $notifier = spyNotifier();
    $useCase = new SubscribeToWaitlist($waitlist, $notifier, new SpyConsentLedger);

    $useCase->execute('vigia@serra.com', 'home');
    $useCase->execute('VIGIA@serra.com', 'loja');

    expect($waitlist->rows)->toHaveCount(1)
        ->and($notifier->sent)->toHaveCount(1);
});

it('records the newsletter consent once, with its version and the subscription', function () {
    $consents = new SpyConsentLedger;
    $useCase = new SubscribeToWaitlist(fakeWaitlist(), spyNotifier(), $consents);

    $useCase->execute('vigia@serra.com', 'home');
    $useCase->execute('vigia@serra.com', 'home');

    expect($consents->records)->toHaveCount(1)
        ->and($consents->records[0]['type'])->toBe(ConsentType::Newsletter)
        ->and($consents->records[0]['version'])->toBe(ConsentType::Newsletter->textVersion())
        ->and($consents->records[0]['email'])->toBe('vigia@serra.com')
        ->and($consents->records[0]['subject'])->toBe('avise-me:1');
});
