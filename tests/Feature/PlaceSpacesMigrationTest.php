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

it('gives the hangar and the indoor museum their illustrations, keeping one chosen in the panel', function () {
    PlaceSpace::query()->whereIn('slug', ['hangar', 'museu-coberto'])->update(['concept_image_path' => null]);
    PlaceSpace::query()->where('slug', 'museu-coberto')->update(['concept_image_path' => 'concept/painel.webp']);

    (require database_path('migrations/2026_10_05_140000_fill_place_space_illustrations.php'))->up();

    expect(PlaceSpace::query()->where('slug', 'hangar')->value('concept_image_path'))->toBe('hangar')
        ->and(PlaceSpace::query()->where('slug', 'museu-coberto')->value('concept_image_path'))->toBe('concept/painel.webp');
});

it('has every planned space illustrated in the concept manifest', function () {
    $manifest = json_decode((string) file_get_contents(resource_path('js/data/concept.json')), true);

    expect(array_column(require database_path('data/place_spaces.php'), 'concept'))->each(fn ($slug) => $slug->toBeIn(array_keys($manifest)));
});
