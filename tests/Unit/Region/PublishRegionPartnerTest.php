<?php

use App\Application\Region\UseCases\PublishRegionPartner;
use App\Domain\Region\Contracts\RegionPartnerRepository;
use App\Domain\Region\PartnerType;
use App\Domain\Region\PartnerWithoutConsent;

function partnersWith(?DateTimeInterface $consent): RegionPartnerRepository
{
    return new class($consent) implements RegionPartnerRepository
    {
        public ?DateTimeInterface $publishedAt = null;

        public function __construct(private ?DateTimeInterface $consent) {}

        public function featuredPublished(int $limit): array
        {
            return [];
        }

        public function published(?PartnerType $type = null, ?string $search = null): array
        {
            return [];
        }

        public function countPublished(): int
        {
            return 0;
        }

        public function findPublished(string $slug): ?array
        {
            return null;
        }

        public function findForPublication(string $slug): ?array
        {
            return ['slug' => $slug, 'consentGivenAt' => $this->consent];
        }

        public function markPublished(string $slug, DateTimeInterface $at): void
        {
            $this->publishedAt = $at;
        }
    };
}

it('refuses to publish a partner without recorded consent', function () {
    $partners = partnersWith(null);

    expect(fn () => (new PublishRegionPartner($partners))->execute('pousada', now()))->toThrow(PartnerWithoutConsent::class)
        ->and($partners->publishedAt)->toBeNull();
});

it('publishes once consent is recorded', function () {
    $partners = partnersWith(now()->subWeek());

    (new PublishRegionPartner($partners))->execute('pousada', $at = now());

    expect($partners->publishedAt)->toBe($at);
});
