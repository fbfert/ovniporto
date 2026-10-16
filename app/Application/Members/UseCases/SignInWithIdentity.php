<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Members\Data\Identity;
use Carbon\CarbonImmutable;

final readonly class SignInWithIdentity
{
    public function __construct(private MemberRepository $members) {}

    /**
     * The first sign-in creates the member; later ones only refresh its identity. Every sign-in
     * is the member's last access and restarts the inactivity cycle.
     *
     * @return array{memberId: int, isNew: bool}
     */
    public function execute(Identity $identity): array
    {
        $existing = $this->members->findIdByProviderId($identity->providerId);
        if ($existing !== null) {
            $this->members->refreshIdentity($existing, $identity);
        }
        $memberId = $existing ?? $this->members->createFromIdentity($identity);
        $this->members->touchLastSeen($memberId, CarbonImmutable::now());

        return ['memberId' => $memberId, 'isNew' => $existing === null];
    }
}
