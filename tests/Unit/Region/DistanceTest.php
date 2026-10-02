<?php

use App\Domain\Region\Distance;

it('measures straight-line distances in whole km', function () {
    // São Paulo (Praça da Sé) → Rio de Janeiro (Centro): about 361 km in a straight line
    expect(Distance::km(-23.5505, -46.6333, -22.9068, -43.1729))->toBe(361);
});

it('is zero at the same point and rounds to the nearest km', function () {
    expect(Distance::km(-27.85495, -50.21841, -27.85495, -50.21841))->toBe(0)
        // ~12.4 km due north of the OVNIPORTO (0.1115° of latitude)
        ->and(Distance::km(-27.85495, -50.21841, -27.74345, -50.21841))->toBe(12);
});
