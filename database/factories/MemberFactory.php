<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Member> */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'google_id' => (string) fake()->unique()->numerify('1###############'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'avatar_url' => null,
            'nickname' => fake()->unique()->regexify('[a-z]{5}[0-9]{3}'),
            'city' => 'Lages',
            'role' => 'member',
            'terms_accepted_at' => now(),
        ];
    }

    /** Just signed in with Google: no nickname, no terms yet. */
    public function incomplete(): static
    {
        return $this->state(['nickname' => null, 'terms_accepted_at' => null]);
    }

    public function role(string $role): static
    {
        return $this->state(['role' => $role]);
    }
}
