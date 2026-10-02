<?php

use App\Application\Members\UseCases\SignInWithIdentity;
use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;

/** In-memory members, enough for the sign-in use case. */
function memoryMembers(): MemberRepository
{
    return new class implements MemberRepository
    {
        /** @var array<int, array{providerId: string, name: string, email: string}> */
        public array $rows = [];

        public function countActive(): int
        {
            return 0;
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
            $this->rows[$id] = ['providerId' => $identity->providerId, 'name' => $identity->name, 'email' => $identity->email];

            return $id;
        }

        public function refreshIdentity(int $memberId, Identity $identity): void
        {
            $this->rows[$memberId]['name'] = $identity->name;
        }

        public function nicknameTaken(string $nickname, ?int $exceptMemberId = null): bool
        {
            return false;
        }

        public function completeProfile(int $memberId, string $nickname, ?string $city, DateTimeInterface $termsAcceptedAt): void {}

        public function updateProfile(int $memberId, string $nickname, ?string $city): void {}

        public function find(int $memberId): ?array
        {
            return null;
        }

        public function delete(int $memberId): void {}
    };
}

it('creates the member on the first sign-in only', function () {
    $members = memoryMembers();
    $signIn = new SignInWithIdentity($members);
    $identity = new Identity('google-1', 'Ana Souza', 'ana@example.test', null);

    $first = $signIn->execute($identity);
    $second = $signIn->execute(new Identity('google-1', 'Ana S.', 'ana@example.test', null));

    expect($first)->toBe(['memberId' => 1, 'isNew' => true])
        ->and($second)->toBe(['memberId' => 1, 'isNew' => false])
        ->and($members->rows)->toHaveCount(1)
        ->and($members->rows[1]['name'])->toBe('Ana S.');
});
