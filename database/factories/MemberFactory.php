<?php

namespace Database\Factories;

use App\Application\Privacy\UseCases\CurrentTermsVersion;
use App\Domain\Privacy\ConsentType;
use App\Models\Consent;
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

    /** A member who accepted the terms has that acceptance in the consent ledger, with the current version. */
    public function configure(): static
    {
        return $this->afterCreating(function (Member $member) {
            if ($member->terms_accepted_at !== null) {
                Consent::query()->create([
                    'type' => ConsentType::Terms->value,
                    'version' => app(CurrentTermsVersion::class)->execute(),
                    'member_id' => $member->id,
                    'given_at' => $member->terms_accepted_at,
                ]);
            }
        });
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
