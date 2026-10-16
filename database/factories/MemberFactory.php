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
            'last_seen_at' => now(),
        ];
    }

    /**
     * A member who accepted the terms has that acceptance and the minimum age declaration
     * in the consent ledger, with the current versions.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Member $member) {
            if ($member->terms_accepted_at !== null) {
                $this->recordConsent($member, ConsentType::Terms, app(CurrentTermsVersion::class)->execute());
                $this->recordConsent($member, ConsentType::MinimumAge, ConsentType::MinimumAge->textVersion());
            }
        });
    }

    /** Signed up before the minimum age declaration existed: the terms are there, the age is not. */
    public function withoutAgeDeclaration(): static
    {
        return $this->afterCreating(fn (Member $member) => Consent::query()
            ->where('member_id', $member->id)
            ->where('type', ConsentType::MinimumAge->value)
            ->delete());
    }

    private function recordConsent(Member $member, ConsentType $type, string $version): void
    {
        Consent::query()->create([
            'type' => $type->value,
            'version' => $version,
            'member_id' => $member->id,
            'given_at' => $member->terms_accepted_at,
        ]);
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

    /** Last access at that moment: the inactivity cycle counts from it. */
    public function lastSeen(\DateTimeInterface $at): static
    {
        return $this->state(['last_seen_at' => $at]);
    }
}
