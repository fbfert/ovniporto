<?php

namespace App\Domain\Campaign;

use App\Domain\Campaign\Data\CampaignSettings;

/**
 * Decides what the public may see of the campaign. Money only exists in the
 * view while the campaign is open: in planning there is no scoreboard, no
 * amount and no crowdfunding link to leak, even if a URL was filled in.
 */
final class CampaignPublicView
{
    /**
     * @param  list<string>  $supporterNames  already filtered by publication consent
     * @param  list<array{name: string, tier: ?string, url: ?string, logo: ?string}>  $sponsors
     * @return array{status: string, storeSharePercent: ?float, open?: array{goalCents: ?int, raisedCents: int, crowdfundingUrl: ?string, platform: ?string, supporters: list<string>, sponsors: list<array{name: string, tier: ?string, url: ?string, logo: ?string}>}}
     */
    public static function build(CampaignSettings $settings, array $supporterNames, array $sponsors): array
    {
        $view = [
            'status' => $settings->status->value,
            'storeSharePercent' => $settings->storeSharePercent,
        ];

        if ($settings->status !== CampaignStatus::Open) {
            return $view;
        }

        $view['open'] = [
            'goalCents' => $settings->goalCents,
            'raisedCents' => $settings->raisedCents ?? 0,
            'crowdfundingUrl' => $settings->crowdfundingUrl,
            'platform' => self::platform($settings->crowdfundingUrl),
            'supporters' => $supporterNames,
            'sponsors' => $sponsors,
        ];

        return $view;
    }

    /** "https://www.catarse.me/ovniporto" → "Catarse": the button reads "Apoiar no Catarse". */
    private static function platform(?string $url): ?string
    {
        $host = $url ? parse_url($url, PHP_URL_HOST) : null;
        if (! is_string($host)) {
            return null;
        }
        $parts = explode('.', preg_replace('/^www\./', '', $host) ?? $host);

        return ucfirst($parts[0]);
    }
}
