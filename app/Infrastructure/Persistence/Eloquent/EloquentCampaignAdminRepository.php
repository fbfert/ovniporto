<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Campaign\Contracts\CampaignAdminRepository;
use App\Domain\Campaign\Data\CampaignSettings;
use App\Models\CampaignSetting;
use App\Models\Sponsor;
use App\Models\Supporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class EloquentCampaignAdminRepository implements CampaignAdminRepository
{
    public function saveSettings(CampaignSettings $settings): void
    {
        $row = CampaignSetting::query()->first() ?? new CampaignSetting;
        $row->fill([
            'status' => $settings->status->value,
            'goal_cents' => $settings->goalCents,
            'raised_cents' => $settings->raisedCents,
            'crowdfunding_url' => $settings->crowdfundingUrl,
            'store_share_percent' => $settings->storeSharePercent,
        ])->save();
    }

    public function supporters(): array
    {
        return Supporter::query()
            ->orderByDesc('supported_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Supporter $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'amountCents' => $s->amount_cents,
                'reward' => $s->reward,
                'publishName' => $s->publish_name,
                'supportedAt' => $s->supported_at?->toDateString(),
            ])
            ->values()
            ->all();
    }

    public function addSupporters(array $entries): void
    {
        DB::transaction(function () use ($entries) {
            foreach ($entries as $entry) {
                Supporter::query()->create([
                    'name' => $entry->name,
                    'amount_cents' => $entry->amountCents,
                    'reward' => $entry->reward,
                    'publish_name' => $entry->publishName,
                    'supported_at' => $entry->supportedAt,
                ]);
            }
        });
    }

    public function deleteSupporter(int $id): void
    {
        Supporter::query()->whereKey($id)->delete();
    }

    public function sponsors(): array
    {
        return Sponsor::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Sponsor $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'tier' => $s->tier,
                'url' => $s->url,
                'logo' => $s->logo_path ? Storage::disk('public')->url($s->logo_path) : null,
            ])
            ->values()
            ->all();
    }

    public function addSponsor(string $name, ?string $tier, ?string $url, ?string $logoPath): int
    {
        return Sponsor::query()->create([
            'name' => $name,
            'tier' => $tier,
            'url' => $url,
            'logo_path' => $logoPath,
            'sort_order' => (int) Sponsor::query()->max('sort_order') + 1,
        ])->id;
    }

    public function deleteSponsor(int $id): ?string
    {
        $sponsor = Sponsor::query()->find($id);
        if ($sponsor === null) {
            return null;
        }
        $sponsor->delete();

        return $sponsor->logo_path;
    }
}
