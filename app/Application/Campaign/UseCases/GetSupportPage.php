<?php

namespace App\Application\Campaign\UseCases;

use App\Domain\Campaign\CampaignPublicView;
use App\Domain\Campaign\CampaignStatus;
use App\Domain\Campaign\Contracts\CampaignRepository;

final readonly class GetSupportPage
{
    public function __construct(private CampaignRepository $campaign) {}

    /** @return array<string, mixed> the public view of the campaign (see CampaignPublicView) */
    public function execute(): array
    {
        $settings = $this->campaign->settings();
        $open = $settings->status === CampaignStatus::Open;

        return CampaignPublicView::build(
            $settings,
            $open ? $this->campaign->publishableSupporterNames() : [],
            $open ? $this->campaign->sponsors() : [],
        );
    }
}
