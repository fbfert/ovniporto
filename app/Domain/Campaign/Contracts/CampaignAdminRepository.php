<?php

namespace App\Domain\Campaign\Contracts;

use App\Domain\Campaign\Data\CampaignSettings;
use App\Domain\Campaign\Data\SupporterEntry;

/** The campaign as the admin edits it: settings, supporters and sponsors. */
interface CampaignAdminRepository
{
    public function saveSettings(CampaignSettings $settings): void;

    /** @return list<array{id: int, name: string, amountCents: ?int, reward: ?string, publishName: bool, supportedAt: ?string}> */
    public function supporters(): array;

    /** @param list<SupporterEntry> $entries */
    public function addSupporters(array $entries): void;

    public function deleteSupporter(int $id): void;

    /** @return list<array{id: int, name: string, tier: ?string, url: ?string, logo: ?string}> */
    public function sponsors(): array;

    public function addSponsor(string $name, ?string $tier, ?string $url, ?string $logoPath): int;

    /** @return string|null the logo path to erase */
    public function deleteSponsor(int $id): ?string;
}
