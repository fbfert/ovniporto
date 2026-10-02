<?php

namespace App\Application\Members\UseCases;

use App\Domain\Members\Contracts\MemberRepository;
use App\Domain\Sightings\Contracts\MemberSightingRepository;

final readonly class GetAccountPage
{
    public function __construct(
        private MemberRepository $members,
        private MemberSightingRepository $sightings,
    ) {}

    /** @return array{profile: array<string, mixed>, sightings: list<array<string, mixed>>, orders: list<array<string, mixed>>}|null */
    public function execute(int $memberId): ?array
    {
        $profile = $this->members->find($memberId);
        if ($profile === null) {
            return null;
        }
        unset($profile['id']);

        return [
            'profile' => $profile,
            'sightings' => $this->sightings->ownedBy($memberId),
            // The store has no checkout yet (add-checkout-payments): there are no orders to list.
            'orders' => [],
        ];
    }
}
