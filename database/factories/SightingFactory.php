<?php

namespace Database\Factories;

use App\Domain\Sightings\SightingStatus;
use App\Domain\Sightings\SightingType;
use App\Models\Sighting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sighting> */
class SightingFactory extends Factory
{
    protected $model = Sighting::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(SightingType::cases()),
            'description' => fake()->sentence(12),
            'observed_date' => fake()->dateTimeBetween('-3 months'),
            'observed_time_kind' => 'range',
            'observed_time_range' => 'night',
            'lat' => -27.85 + fake()->randomFloat(4, -0.2, 0.2),
            'lng' => -50.21 + fake()->randomFloat(4, -0.2, 0.2),
            'place_label' => 'Lages',
            'public_nickname' => fake()->firstName(),
            'consent_given_at' => now(),
            'status' => SightingStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => SightingStatus::Approved,
            'published_at' => now()->subHour(),
        ]);
    }
}
