<?php

use App\Domain\Campaign\CampaignPublicView;
use App\Domain\Campaign\CampaignStatus;
use App\Domain\Campaign\Data\CampaignSettings;

it('shows nothing about money while planning, even with a URL filled in by mistake', function () {
    $view = CampaignPublicView::build(
        new CampaignSettings(CampaignStatus::Planning, goalCents: 5_000_000, raisedCents: 120_000, crowdfundingUrl: 'https://www.catarse.me/ovniporto'),
        ['Ana'],
        [['name' => 'Padaria', 'tier' => null, 'url' => null, 'logo' => null]],
    );

    expect($view)->toBe(['status' => 'planning', 'storeSharePercent' => null])
        ->and(json_encode($view))->not->toContain('catarse');
});

it('opens the scoreboard, the link and the wall only when the campaign is open', function () {
    $view = CampaignPublicView::build(
        new CampaignSettings(CampaignStatus::Open, goalCents: 5_000_000, crowdfundingUrl: 'https://www.catarse.me/ovniporto'),
        ['Ana', 'Bruno'],
        [],
    );

    expect($view['open'])->toMatchArray([
        'goalCents' => 5_000_000,
        'raisedCents' => 0,
        'crowdfundingUrl' => 'https://www.catarse.me/ovniporto',
        'platform' => 'Catarse',
        'supporters' => ['Ana', 'Bruno'],
    ]);
});

it('keeps a closed campaign without a payment link', function () {
    $view = CampaignPublicView::build(new CampaignSettings(CampaignStatus::Closed, crowdfundingUrl: 'https://x.test'), [], []);

    expect($view)->not->toHaveKey('open');
});

it('starts in planning with everything else empty', function () {
    expect(CampaignSettings::initial())->toEqual(new CampaignSettings(CampaignStatus::Planning));
});
