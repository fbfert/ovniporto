<?php

use App\Models\RegionPartner;
use App\Models\Sighting;
use Illuminate\Support\Facades\Storage;

it('refuses to seed demo data in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('dev:seed-demo')->assertFailed();

    expect(Sighting::query()->count())->toBe(0);
});

it('seeds and clears clearly marked demo data locally', function () {
    Storage::fake('public');

    $this->artisan('dev:seed-demo')->assertSuccessful();

    expect(Sighting::query()->approved()->where('is_demo', true)->count())->toBe(12)
        ->and(RegionPartner::query()->where('name', 'like', '%[EXEMPLO]%')->count())->toBe(3);

    $this->artisan('dev:clear-demo')->assertSuccessful();

    expect(Sighting::query()->count())->toBe(0)
        ->and(RegionPartner::query()->count())->toBe(0);
});
