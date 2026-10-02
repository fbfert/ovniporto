<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Campaign\CampaignStatus;
use App\Domain\Campaign\Contracts\CampaignRepository;
use App\Domain\Campaign\Data\CampaignSettings;
use App\Models\CampaignSetting;
use App\Models\Sponsor;
use App\Models\Supporter;
use Illuminate\Support\Facades\Storage;

final class EloquentCampaignRepository implements CampaignRepository
{
    public function settings(): CampaignSettings
    {
        $row = CampaignSetting::query()->first();
        if ($row === null) {
            return CampaignSettings::initial();
        }

        return new CampaignSettings(
            status: CampaignStatus::tryFrom((string) $row->status) ?? CampaignStatus::Planning,
            goalCents: $row->goal_cents === null ? null : (int) $row->goal_cents,
            raisedCents: $row->raised_cents === null ? null : (int) $row->raised_cents,
            crowdfundingUrl: $row->crowdfunding_url ?: null,
            storeSharePercent: $row->store_share_percent === null ? null : (float) $row->store_share_percent,
        );
    }

    public function publishableSupporterNames(): array
    {
        return Supporter::query()
            ->where('publish_name', true)
            ->orderBy('supported_at')
            ->orderBy('id')
            ->pluck('name')
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();
    }

    public function sponsors(): array
    {
        return Sponsor::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Sponsor $sponsor) => [
                'name' => (string) $sponsor->name,
                'tier' => $sponsor->tier,
                'url' => $sponsor->url,
                'logo' => $sponsor->logo_path ? Storage::disk('public')->url($sponsor->logo_path) : null,
            ])
            ->values()
            ->all();
    }
}
