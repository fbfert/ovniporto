<?php

namespace App\Application\Region\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Content\Contracts\ImageLibrary;
use App\Domain\Map\Contracts\Geocoder;
use App\Domain\Privacy\ConsentType;
use App\Domain\Privacy\Contracts\ConsentLedger;
use App\Domain\Region\Contracts\ConsentProofStorage;
use App\Domain\Region\Contracts\RegionAdminRepository;
use App\Domain\Region\Data\PartnerFields;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * /painel/regiao, admin only. A partner never goes public without the date
 * its consent was received: PublishRegionPartner (the domain rule) refuses it,
 * whatever the form showed.
 */
final readonly class ManageRegionPartners
{
    public function __construct(
        private RegionAdminRepository $partners,
        private PublishRegionPartner $publisher,
        private Geocoder $geocoder,
        private ImageLibrary $images,
        private ConsentProofStorage $proofs,
        private Auditor $auditor,
        private ConsentLedger $consents,
    ) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->partners->all();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        return $this->partners->find($id);
    }

    /** @return array{lat: float, lng: float}|null */
    public function locate(string $address): ?array
    {
        return $this->geocoder->locate($address);
    }

    public function create(int $actorId, PartnerFields $fields): int
    {
        $id = $this->auditor->audited(
            new AuditEntry($actorId, 'region.created', 'partner', 0, ['name' => $fields->name]),
            fn () => null,
            fn () => $this->partners->create($fields),
        );
        $this->recordConsent($id, $fields->consentGivenAt);

        return $id;
    }

    public function update(int $actorId, int $id, PartnerFields $fields): void
    {
        $before = $this->partners->find($id);
        $this->auditor->audited(
            new AuditEntry($actorId, 'region.updated', 'partner', $id),
            fn () => $before,
            fn () => $this->partners->update($id, $fields),
        );
        if ($fields->consentGivenAt?->format('Y-m-d') !== ($before['consentGivenAt'] ?? null)) {
            $this->recordConsent($id, $fields->consentGivenAt);
        }
    }

    /** The partner's yes to being listed, on the date they gave it; IP and browser are of who registered it. */
    private function recordConsent(int $partnerId, ?DateTimeImmutable $givenAt): void
    {
        if ($givenAt !== null) {
            $this->consents->record(
                ConsentType::PartnerListing,
                ConsentType::PartnerListing->textVersion(),
                subject: "parceiro:{$partnerId}",
                givenAt: $givenAt,
            );
        }
    }

    public function setCover(int $actorId, int $id, string $image): void
    {
        $path = $this->images->store($image, 'partners');
        $this->record($actorId, 'region.cover_uploaded', $id, fn () => $this->images->delete($this->partners->setCover($id, $path)));
    }

    public function setConsentProof(int $actorId, int $id, string $contents, string $extension): void
    {
        $path = $this->proofs->put($contents, $extension);
        $this->record($actorId, 'region.consent_proof_uploaded', $id, fn () => $this->proofs->delete($this->partners->setConsentProof($id, $path)));
    }

    /** @return array{contents: string, extension: string}|null */
    public function consentProof(int $id): ?array
    {
        $path = $this->partners->consentProofOf($id);
        if ($path === null) {
            return null;
        }
        $contents = $this->proofs->get($path);

        return $contents === null ? null : ['contents' => $contents, 'extension' => pathinfo($path, PATHINFO_EXTENSION)];
    }

    /** PartnerWithoutConsent when the consent date is missing. */
    public function publish(int $actorId, int $id): void
    {
        $slug = $this->partners->slugOf($id) ?? throw new InvalidArgumentException('Parceiro não encontrado.');
        $this->record($actorId, 'region.published', $id, fn () => $this->publisher->execute($slug, new DateTimeImmutable));
    }

    public function unpublish(int $actorId, int $id): void
    {
        $this->record($actorId, 'region.unpublished', $id, fn () => $this->partners->unpublish($id));
    }

    public function delete(int $actorId, int $id): void
    {
        $this->record($actorId, 'region.deleted', $id, function () use ($id) {
            foreach ($this->partners->delete($id) as $path) {
                str_starts_with($path, 'consents/') ? $this->proofs->delete($path) : $this->images->delete($path);
            }
        });
    }

    private function record(int $actorId, string $action, int $id, callable $change): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, $action, 'partner', $id),
            fn () => $this->partners->find($id),
            $change,
        );
    }
}
