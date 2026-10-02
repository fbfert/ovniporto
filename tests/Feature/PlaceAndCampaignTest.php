<?php

use App\Models\CampaignSetting;
use App\Models\ConstructionPost;
use App\Models\PlaceSpace;
use App\Models\Supporter;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function post(array $attributes = []): ConstructionPost
{
    static $n = 0;
    $n++;

    return ConstructionPost::query()->create([
        'title' => "Post {$n}",
        'slug' => "post-{$n}",
        'body' => 'A **primeira pedra**.',
        'phase' => 1,
        'published_at' => now()->subDay(),
        ...$attributes,
    ]);
}

it('seeds the 9 spaces in 4 phases and a campaign in planning', function () {
    expect(PlaceSpace::query()->selectRaw('phase, count(*) as total')->groupBy('phase')->pluck('total', 'phase')->all())
        ->toEqual([1 => 5, 2 => 1, 3 => 1, 4 => 2])
        ->and(CampaignSetting::query()->sole()->only(['status', 'goal_cents', 'raised_cents', 'crowdfunding_url', 'store_share_percent']))
        ->toBe(['status' => 'planning', 'goal_cents' => null, 'raised_cents' => null, 'crowdfunding_url' => null, 'store_share_percent' => null]);
});

it('shows the place as a plan, with every space still planned', function () {
    $this->get('/o-lugar')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Place/Place')
        ->has('spaces', 9)
        ->where('spaces', fn ($spaces) => collect($spaces)->every(fn ($space) => $space['status'] !== 'open'))
        ->has('photos', 0)
    );
});

it('never asks for money on /apoie while planning, even with a URL set', function () {
    CampaignSetting::query()->update(['crowdfunding_url' => 'https://www.catarse.me/ovniporto', 'raised_cents' => 99_00]);

    $this->get('/apoie')
        ->assertOk()
        ->assertDontSee('catarse', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Place/Support')
            ->where('campaign.status', 'planning')
            ->missing('campaign.open')
        );
});

it('lists only consenting supporters once the campaign is open', function () {
    CampaignSetting::query()->update(['status' => 'open', 'goal_cents' => 1_000_000, 'crowdfunding_url' => 'https://www.catarse.me/ovniporto']);
    Supporter::query()->create(['name' => 'Ana Publica', 'publish_name' => true]);
    Supporter::query()->create(['name' => 'Bruno Privado', 'publish_name' => false]);

    $this->get('/apoie')
        ->assertDontSee('Bruno Privado')
        ->assertInertia(fn (Assert $page) => $page
            ->where('campaign.open.supporters', ['Ana Publica'])
            ->where('campaign.open.platform', 'Catarse')
        );
});

it('shows the empty diary while the work has not started', function () {
    $this->get('/obra')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Place/Diary')->has('posts', 0));
});

it('hides scheduled posts from the list, the page and the feed', function () {
    $published = post(['title' => 'Primeira pedra']);
    $scheduled = post(['title' => 'Post do futuro', 'published_at' => now()->addWeek()]);

    $this->get('/obra')->assertInertia(fn (Assert $page) => $page->has('posts', 1)->where('posts.0.slug', $published->slug));
    $this->get("/obra/{$scheduled->slug}")->assertNotFound();
    $this->get('/obra/nao-existe')->assertNotFound();
    $this->get('/obra.rss')->assertDontSee('Post do futuro');
});

it('renders a published post with its markdown', function () {
    $published = post();

    $this->get("/obra/{$published->slug}")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Place/DiaryPost')
        ->where('post.bodyHtml', fn (string $html) => str_contains($html, '<strong>primeira pedra</strong>'))
    );
});

it('serves a valid RSS 2.0 feed with absolute links', function () {
    post(['title' => 'Primeira pedra', 'slug' => 'primeira-pedra']);

    $response = $this->get('/obra.rss')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    $xml = simplexml_load_string($response->getContent());

    expect($xml)->not->toBeFalse()
        ->and((string) $xml['version'])->toBe('2.0')
        ->and(count($xml->channel->item))->toBe(1)
        ->and((string) $xml->channel->item[0]->link)->toBe(url('/obra/primeira-pedra'))
        ->and((string) $xml->channel->item[0]->pubDate)->not->toBe('');
});
