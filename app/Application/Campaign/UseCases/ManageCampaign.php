<?php

namespace App\Application\Campaign\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Campaign\Contracts\CampaignAdminRepository;
use App\Domain\Campaign\Contracts\CampaignRepository;
use App\Domain\Campaign\Data\CampaignSettings;
use App\Domain\Campaign\Data\SupporterEntry;
use App\Domain\Campaign\SupporterCsv;
use App\Domain\Content\Contracts\ImageLibrary;

/**
 * /painel/campanha, admin only. The status, the money and the names on the
 * supporters wall all change from here, and every change is audited.
 */
final readonly class ManageCampaign
{
    public function __construct(
        private CampaignRepository $campaign,
        private CampaignAdminRepository $admin,
        private ImageLibrary $images,
        private Auditor $auditor,
    ) {}

    /** @return array{settings: array<string, mixed>, supporters: list<array<string, mixed>>, sponsors: list<array<string, mixed>>} */
    public function page(): array
    {
        $settings = $this->campaign->settings();

        return [
            'settings' => $this->settingsArray($settings),
            'supporters' => $this->admin->supporters(),
            'sponsors' => $this->admin->sponsors(),
        ];
    }

    public function saveSettings(int $actorId, CampaignSettings $settings): void
    {
        $this->auditor->audited(
            new AuditEntry($actorId, 'campaign.updated', 'campaign', 1),
            fn () => $this->settingsArray($this->campaign->settings()),
            fn () => $this->admin->saveSettings($settings),
        );
    }

    public function addSupporter(int $actorId, SupporterEntry $entry): void
    {
        $this->record($actorId, 'campaign.supporter_added', ['name' => $entry->name], fn () => $this->admin->addSupporters([$entry]));
    }

    /** @return int how many supporters the spreadsheet brought (InvalidArgumentException tells the line that failed) */
    public function importSupporters(int $actorId, string $csv): int
    {
        $entries = SupporterCsv::parse($csv);
        $this->record($actorId, 'campaign.supporters_imported', ['count' => count($entries)], fn () => $this->admin->addSupporters($entries));

        return count($entries);
    }

    public function deleteSupporter(int $actorId, int $id): void
    {
        $this->record($actorId, 'campaign.supporter_deleted', ['id' => $id], fn () => $this->admin->deleteSupporter($id));
    }

    public function addSponsor(int $actorId, string $name, ?string $tier, ?string $url, ?string $logo): void
    {
        $path = $logo === null ? null : $this->images->store($logo, 'sponsors');
        $this->record($actorId, 'campaign.sponsor_added', ['name' => $name], fn () => $this->admin->addSponsor($name, $tier, $url, $path));
    }

    public function deleteSponsor(int $actorId, int $id): void
    {
        $this->record($actorId, 'campaign.sponsor_deleted', ['id' => $id], fn () => $this->images->delete($this->admin->deleteSponsor($id)));
    }

    /** @param array<string, mixed> $context */
    private function record(int $actorId, string $action, array $context, callable $change): void
    {
        $this->auditor->audited(new AuditEntry($actorId, $action, 'campaign', 1, $context), fn () => null, $change);
    }

    /** @return array<string, mixed> */
    private function settingsArray(CampaignSettings $settings): array
    {
        return [
            'status' => $settings->status->value,
            'goalCents' => $settings->goalCents,
            'raisedCents' => $settings->raisedCents,
            'crowdfundingUrl' => $settings->crowdfundingUrl,
            'storeSharePercent' => $settings->storeSharePercent,
        ];
    }
}
