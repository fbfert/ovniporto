<?php

use App\Domain\Map\Contracts\Geocoder;
use App\Models\AuditLog;
use App\Models\ConstructionPost;
use App\Models\ContentBlock;
use App\Models\Faq;
use App\Models\Member;
use App\Models\NewsletterSubscriber;
use App\Models\PlaceSpace;
use App\Models\RegionPartner;
use App\Models\Supporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\JpegWithExif;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
    $this->admin = Member::factory()->role('admin')->create();
});

function jpeg(string $name = 'foto.jpg'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, JpegWithExif::make(1200, 800));
}

it('keeps every content screen admin-only', function (string $path) {
    $this->actingAs(Member::factory()->role('moderator')->create())->get($path)->assertForbidden();
    $this->actingAs(Member::factory()->role('store')->create())->get($path)->assertForbidden();
    $this->actingAs($this->admin)->get($path)->assertOk();
})->with(['/painel/conteudo', '/painel/configuracoes', '/painel/lugar', '/painel/obra', '/painel/regiao', '/painel/campanha', '/painel/avise-me']);

it('refuses content writes to a moderator, even by direct request', function () {
    $this->actingAs(Member::factory()->role('moderator')->create())
        ->put('/painel/configuracoes/links', ['contact_email' => 'x@example.com'])
        ->assertForbidden();
});

it('saves links and goals, audited', function () {
    $this->actingAs($this->admin)->put('/painel/configuracoes/links', [
        'link_whatsapp' => 'https://chat.whatsapp.com/abc',
        'link_instagram' => '',
        'contact_email' => 'oi@ovniporto.tars.art.br',
    ])->assertSessionHasNoErrors();
    $this->put('/painel/configuracoes/metas', ['launch_date' => '2026-11-01', 'goal_members' => 500])->assertSessionHasNoErrors();

    expect(ContentBlock::query()->where('key', 'link_whatsapp')->value('value'))->toBe('https://chat.whatsapp.com/abc')
        ->and(ContentBlock::query()->where('key', 'goal_members')->value('value'))->toBe('500')
        ->and(AuditLog::query()->where('action', 'settings.updated')->count())->toBe(2);
});

it('previews markdown with the same renderer as the site', function () {
    $this->actingAs($this->admin)
        ->postJson('/painel/previa', ['markdown' => "## Céu\n\n**escuro**"])
        ->assertOk()
        ->assertJsonPath('html', fn (string $html) => str_contains($html, '<h2>Céu</h2>') && str_contains($html, '<strong>escuro</strong>'));
});

it('dates a legal text and lifts the draft notice only when marked final', function () {
    $this->actingAs($this->admin)->put('/painel/configuracoes/textos/privacy_body', ['value' => '## Dados', 'final' => true]);

    expect(ContentBlock::query()->where('key', 'privacy_final')->value('value'))->toBe('1')
        ->and(ContentBlock::query()->where('key', 'privacy_updated_at')->value('value'))->toBe(now()->toDateString());

    // A final text is a new version of the terms: the admin agrees to it too before going on.
    $this->put('/painel/configuracoes/textos/nao_existe', ['value' => 'x'])->assertRedirect('/termos/aceitar');
    $this->post('/termos/aceitar', ['terms' => '1']);
    $this->put('/painel/configuracoes/textos/nao_existe', ['value' => 'x'])->assertNotFound();
});

it('edits and reorders the FAQ', function () {
    $first = Faq::query()->create(['question' => 'Primeira?', 'answer' => 'Sim.', 'sort_order' => 0]);
    $second = Faq::query()->create(['question' => 'Segunda?', 'answer' => 'Sim.', 'sort_order' => 1]);

    $this->actingAs($this->admin)->post("/painel/configuracoes/faq/{$second->id}/mover", ['direction' => -1]);
    $this->post('/painel/configuracoes/faq', ['title' => 'Terceira?', 'body' => 'Talvez.'])->assertSessionHasNoErrors();

    expect(Faq::query()->orderBy('sort_order')->pluck('question')->all())->toBe(['Segunda?', 'Primeira?', 'Terceira?']);
    $this->get('/faq')->assertInertia(fn (Assert $page) => $page->where('faqs.0.question', 'Segunda?'));
});

it('refuses a 3D viewer from a host outside the list', function () {
    $this->actingAs($this->admin)
        ->put('/painel/lugar/mapa-3d', ['embed' => '<iframe src="https://evil.example.com/3d"></iframe>'])
        ->assertSessionHasErrors('embed');
    $this->put('/painel/lugar/mapa-3d', ['embed' => '<iframe width="640" src="https://sketchfab.com/models/abc/embed"></iframe>'])
        ->assertSessionHasNoErrors();

    $this->get('/o-lugar')->assertInertia(fn (Assert $page) => $page->where('map3d', 'https://sketchfab.com/models/abc/embed'));
});

it('publishes terrain photos without camera metadata, alt required', function () {
    $this->actingAs($this->admin)->post('/painel/lugar/fotos', ['image' => jpeg()])->assertSessionHasErrors('alt');
    $this->post('/painel/lugar/fotos', ['image' => jpeg(), 'alt' => 'O terreno ao entardecer'])->assertSessionHasNoErrors();

    $path = collect(Storage::disk('public')->allFiles('site'))->sole();
    expect($path)->toEndWith('.webp')
        ->and(Storage::disk('public')->get($path))->not->toContain('Exif');
    $this->get('/o-lugar')->assertInertia(fn (Assert $page) => $page->where('photos.0.alt', 'O terreno ao entardecer'));
});

it('never offers "open" for a planned space', function () {
    $space = PlaceSpace::query()->create(['slug' => 'pista', 'name' => 'Pista', 'role' => 'Pouso', 'phase' => 1, 'status' => 'planning']);

    $this->actingAs($this->admin)
        ->put("/painel/lugar/espacos/{$space->id}", ['name' => 'Pista', 'role' => 'Pouso', 'phase' => 1, 'status' => 'open'])
        ->assertSessionHasErrors('status');
});

it('keeps a scheduled diary post hidden until its date', function () {
    $this->actingAs($this->admin)->post('/painel/obra', [
        'title' => 'Primeira pedra',
        'body' => 'O dia em que tudo começou.',
        'phase' => 1,
        'publishedAt' => now()->addDays(3)->toDateTimeString(),
    ])->assertSessionHasNoErrors();
    $post = ConstructionPost::query()->sole();

    $this->get('/obra')->assertInertia(fn (Assert $page) => $page->has('posts', 0));
    $this->get("/obra/{$post->slug}")->assertNotFound();

    $this->travel(4)->days();
    $this->get("/obra/{$post->slug}")->assertOk();
});

it('refuses a diary cover without alt text', function () {
    $this->actingAs($this->admin)->post('/painel/obra', [
        'title' => 'Com capa',
        'body' => 'Texto.',
        'phase' => 1,
        'cover' => jpeg(),
    ])->assertSessionHasErrors('coverAlt');
    expect(ConstructionPost::query()->count())->toBe(0);
});

it('never publishes a partner without consent, even by direct request', function () {
    $this->actingAs($this->admin)->post('/painel/regiao', ['name' => 'Pousada da Serra', 'type' => 'inn', 'city' => 'Lages']);
    $partner = RegionPartner::query()->sole();

    $this->post("/painel/regiao/{$partner->id}/publicar")->assertSessionHasErrors('consentGivenAt');
    expect($partner->fresh()->published_at)->toBeNull();
    $this->get('/regiao/pousada-da-serra')->assertNotFound();

    $this->post("/painel/regiao/{$partner->id}", [
        'name' => 'Pousada da Serra', 'type' => 'inn', 'city' => 'Lages', 'consentGivenAt' => now()->toDateString(),
        'consentProof' => UploadedFile::fake()->create('consentimento.pdf', 20, 'application/pdf'),
    ])->assertSessionHasNoErrors();
    $this->post("/painel/regiao/{$partner->id}/publicar")->assertSessionHasNoErrors();
    $this->get('/regiao/pousada-da-serra')->assertOk();

    // The proof stays private: only the panel route serves it.
    $proof = $partner->fresh()->consent_proof_path;
    Storage::disk('local')->assertExists($proof);
    $this->get("/painel/regiao/{$partner->id}/consentimento")->assertOk();
});

it('takes a partner off the site when its consent date is removed', function () {
    $partner = RegionPartner::query()->create([
        'name' => 'Vinícola', 'slug' => 'vinicola', 'type' => 'producer', 'city' => 'Lages',
        'consent_given_at' => now()->subMonth(), 'published_at' => now()->subWeek(),
    ]);

    $this->actingAs($this->admin)->post("/painel/regiao/{$partner->id}", ['name' => 'Vinícola', 'type' => 'producer', 'city' => 'Lages']);

    expect($partner->fresh()->published_at)->toBeNull();
});

it('suggests a point for an address through the geocoder', function () {
    app()->instance(Geocoder::class, new class implements Geocoder
    {
        public function cityAt(float $lat, float $lng): ?string
        {
            return null;
        }

        public function locate(string $address): ?array
        {
            return ['lat' => -27.81, 'lng' => -50.32];
        }
    });

    $this->actingAs($this->admin)
        ->postJson('/painel/regiao/localizar', ['address' => 'Rua Coronel Córdova, Lages'])
        ->assertJsonPath('point.lat', -27.81);
});

it('saves the campaign settings, audited', function () {
    $this->actingAs($this->admin)->put('/painel/campanha', [
        'status' => 'planning',
        'goal' => '150000',
        'storeSharePercent' => '10',
    ])->assertSessionHasNoErrors();

    $this->get('/painel/campanha')->assertInertia(fn (Assert $page) => $page
        ->component('Panel/Content/Campaign')
        ->where('settings.goalCents', 15000000)
        ->where('settings.storeSharePercent', 10)
    );
    expect(AuditLog::query()->where('action', 'campaign.updated')->exists())->toBeTrue();
});

it('imports supporters from CSV, publishing a name only with consent', function () {
    $csv = "nome;valor;recompensa;publicar_nome;data\nAna;50,00;Adesivo;sim;01/03/2027\nBruno;1.200,00;;não;2027-03-02\n";

    $this->actingAs($this->admin)
        ->post('/painel/campanha/apoiadores/importar', ['file' => UploadedFile::fake()->createWithContent('apoio.csv', $csv)])
        ->assertSessionHasNoErrors();

    expect(Supporter::query()->orderBy('id')->get(['name', 'amount_cents', 'publish_name'])->toArray())->toBe([
        ['name' => 'Ana', 'amount_cents' => 5000, 'publish_name' => true],
        ['name' => 'Bruno', 'amount_cents' => 120000, 'publish_name' => false],
    ]);
});

it('tells which CSV line is wrong and imports nothing', function () {
    $csv = "nome,valor\nAna,50\n,10\n";

    $this->actingAs($this->admin)
        ->post('/painel/campanha/apoiadores/importar', ['file' => UploadedFile::fake()->createWithContent('apoio.csv', $csv)])
        ->assertSessionHasErrors(['file' => 'Linha 3: falta o nome.']);
    expect(Supporter::query()->count())->toBe(0);
});

it('lists, exports and removes waitlist subscribers', function () {
    $keep = NewsletterSubscriber::query()->create(['email' => 'fica@example.com', 'source' => 'home', 'consent_text' => 'ok', 'consented_at' => now(), 'confirmed_at' => now()]);
    $gone = NewsletterSubscriber::query()->create(['email' => 'sai@example.com', 'source' => 'apoie', 'consent_text' => 'ok', 'consented_at' => now()]);

    $this->actingAs($this->admin)->get('/painel/avise-me')->assertInertia(fn (Assert $page) => $page->has('subscribers', 2)->where('confirmed', 1));
    $this->delete("/painel/avise-me/{$gone->id}")->assertSessionHasNoErrors();

    $csv = $this->get('/painel/avise-me/exportar')->assertOk()->streamedContent();
    expect($csv)->toContain('fica@example.com')->not->toContain('sai@example.com');
    $this->get('/painel/avise-me')->assertInertia(fn (Assert $page) => $page->has('subscribers', 1)->where('subscribers.0.id', $keep->id));
});
