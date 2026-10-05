<?php

use App\Models\PlaceSpace;
use Inertia\Testing\AssertableInertia as Assert;

it('has the nine planned spaces after migrating, without any seeder', function () {
    $this->get('/o-lugar')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->has('spaces', 9)
        ->where('spaces', fn ($spaces) => collect($spaces)->every(fn ($space) => filled($space['role']) && filled($space['description'])))
        ->etc()
    );
});

it('fills an empty description and keeps what the panel edited', function () {
    PlaceSpace::query()->where('slug', 'hangar')->update(['description' => null]);
    PlaceSpace::query()->where('slug', 'lanchonete')->update(['name' => 'Cantina', 'description' => 'Editado no painel.']);

    (require database_path('migrations/2026_10_05_120000_fill_place_spaces_from_plan.php'))->up();

    expect(PlaceSpace::query()->where('slug', 'hangar')->value('description'))->toBe('Estacionamento para naves terrestres, só com sinalização.')
        ->and(PlaceSpace::query()->where('slug', 'lanchonete')->first()->only(['name', 'description']))->toBe(['name' => 'Cantina', 'description' => 'Editado no painel.'])
        ->and(PlaceSpace::query()->count())->toBe(9);
});
