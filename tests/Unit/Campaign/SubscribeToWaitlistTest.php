<?php

use App\Application\Campaign\UseCases\SubscribeToWaitlist;
use App\Domain\Campaign\Contracts\WaitlistNotifier;
use App\Domain\Campaign\Contracts\WaitlistRepository;

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

    (new SubscribeToWaitlist($waitlist, $notifier))->execute('  Vigia@Serra.com ', 'home');

    expect($waitlist->rows)->toHaveKey('vigia@serra.com')
        ->and($notifier->sent)->toBe(['vigia@serra.com']);
});

it('is idempotent: a second subscription neither duplicates nor re-sends', function () {
    $waitlist = fakeWaitlist();
    $notifier = spyNotifier();
    $useCase = new SubscribeToWaitlist($waitlist, $notifier);

    $useCase->execute('vigia@serra.com', 'home');
    $useCase->execute('VIGIA@serra.com', 'loja');

    expect($waitlist->rows)->toHaveCount(1)
        ->and($notifier->sent)->toHaveCount(1);
});
