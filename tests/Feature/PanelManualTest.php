<?php

use App\Domain\Manual\Contracts\ManualLibrary;
use App\Domain\Manual\InvalidManualData;
use App\Models\Member;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

it('opens the manual to every panel role and refuses common members and guests', function () {
    foreach (['admin', 'moderator', 'store'] as $role) {
        $this->actingAs(Member::factory()->role($role)->create())->get('/painel/manual')->assertOk();
    }
    $this->actingAs(Member::factory()->create())->get('/painel/manual')->assertForbidden();

    auth()->logout();
    $this->get('/painel/manual')->assertRedirect('/entrar');
});

it('lists only the chapters the role may read', function () {
    $this->actingAs(Member::factory()->role('store')->create())
        ->get('/painel/manual')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Manual/Index')
            ->where('chapters', fn ($chapters) => collect($chapters)->pluck('slug')->contains('pedidos')
                && collect($chapters)->pluck('slug')->contains('produtos')
                && ! collect($chapters)->pluck('slug')->intersect(['relatos', 'membros', 'conteudo', 'auditoria'])->count())
        );
});

it('shows a chapter with its map and sections', function () {
    $this->actingAs(Member::factory()->role('store')->create())
        ->get('/painel/manual/produtos')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Manual/Chapter')
            ->where('chapter.slug', 'produtos')
            ->has('chapter.map.children')
            ->has('chapter.sections')
        );
});

it('answers 404 for a chapter of a closed area and for an unknown chapter', function () {
    $this->actingAs(Member::factory()->role('moderator')->create());

    $this->get('/painel/manual/pedidos')->assertNotFound();
    $this->get('/painel/manual/cozinha')->assertNotFound();
});

it('documents every panel route and cites only routes that exist', function () {
    $panelRoutes = collect(Route::getRoutes()->getRoutesByName())->keys()
        ->filter(fn (string $name) => $name === 'panel' || str_starts_with($name, 'panel.'));
    $chapters = collect(app(ManualLibrary::class)->chapters());
    $cited = $chapters->flatMap(fn (array $c) => collect($c['routes'])->map(fn (string $r) => [$r, $c['slug']]));

    $undocumented = $panelRoutes->diff($cited->pluck(0))->values()->all();
    $unknown = $cited->reject(fn (array $pair) => $panelRoutes->contains($pair[0]))->map(fn (array $p) => "{$p[1]}: {$p[0]}")->values()->all();

    expect($undocumented)->toBe([], 'Rotas do painel sem capítulo no manual (resources/content/manual): '.implode(', ', $undocumented))
        ->and($unknown)->toBe([], 'Capítulos do manual citando rotas que não existem: '.implode(', ', $unknown));
});

it('explains every panel screen in some section, so each one gets its "Como funciona" link', function () {
    $downloads = ['.export', '.pdf', '.proof'];
    $screens = collect(Route::getRoutes()->getRoutesByName())
        ->filter(fn ($route, string $name) => in_array('GET', $route->methods(), true)
            && ($name === 'panel' || str_starts_with($name, 'panel.'))
            && ! str_starts_with($name, 'panel.manual')
            && ! Str::endsWith($name, $downloads))
        ->keys();
    $explained = collect(app(ManualLibrary::class)->chapters())->flatMap(fn (array $c) => array_merge(...array_column($c['sections'], 'screens')));

    $missing = $screens->diff($explained)->values()->all();
    expect($missing)->toBe([], 'Telas do painel sem seção com "screen" no manual: '.implode(', ', $missing));
});

it('shares the "Como funciona" link of the current screen, only from chapters the role reads', function () {
    $this->actingAs(Member::factory()->role('store')->create())
        ->get('/painel/produtos/novo')
        ->assertInertia(fn (Assert $page) => $page->where('manualLink', '/painel/manual/produtos#novo'));

    $this->get('/painel/manual')->assertInertia(fn (Assert $page) => $page->where('manualLink', null));
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('manualLink', null));
});

it('keeps the panel up when a manual chapter is broken, dropping only the link', function () {
    $this->app->instance(ManualLibrary::class, new class implements ManualLibrary
    {
        public function chapters(): array
        {
            throw new InvalidManualData('produtos: o campo title não pode ficar vazio');
        }
    });

    $this->actingAs(Member::factory()->role('store')->create())
        ->get('/painel/produtos')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('manualLink', null));
});

it('points every section screen at an existing panel page', function () {
    $routes = Route::getRoutes();
    foreach (app(ManualLibrary::class)->chapters() as $chapter) {
        foreach ($chapter['sections'] as $section) {
            foreach ($section['screens'] as $screen) {
                $route = $routes->getByName($screen);
                expect($route)->not->toBeNull("{$chapter['slug']}#{$section['id']}: tela desconhecida {$screen}")
                    ->and($route?->methods())->toContain('GET');
            }
        }
    }
});
