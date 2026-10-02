<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;

final readonly class SignInWithIdentity
{
    public function __construct(private MemberRepository $members) {}

    /** @return array{memberId: int, isNew: bool} the first sign-in creates the member; later ones only refresh its identity */
    public function execute(Identity $identity): array
    {
        $existing = $this->members->findIdByProviderId($identity->providerId);
        if ($existing !== null) {
            $this->members->refreshIdentity($existing, $identity);

            return ['memberId' => $existing, 'isNew' => false];
        }

        return ['memberId' => $this->members->createFromIdentity($identity), 'isNew' => true];
    }
}
