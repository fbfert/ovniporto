<?php

namespace Database\Seeders;

use App\Domain\Privacy\ConsentType;
use App\Models\Consent;
use App\Models\Member;
use Illuminate\Database\Seeder;

/**
 * Members the end-to-end suite signs in as (through /dev/entrar-como/{id}, local only).
 * Ids are fixed: e2e/support/members.ts mirrors them.
 */
class E2eSeeder extends Seeder
{
    public const MEMBERS = [
        1 => ['nickname' => 'coruja_e2e', 'role' => 'member', 'name' => 'Vigia E2E', 'email' => 'vigia@e2e.test'],
        2 => ['nickname' => 'torre_e2e', 'role' => 'moderator', 'name' => 'Torre E2E', 'email' => 'torre@e2e.test'],
        3 => ['nickname' => 'admin_e2e', 'role' => 'admin', 'name' => 'Admin E2E', 'email' => 'admin@e2e.test'],
        4 => ['nickname' => 'saida_e2e', 'role' => 'member', 'name' => 'Quem Sai E2E', 'email' => 'saida@e2e.test'],
    ];

    public function run(): void
    {
        foreach (self::MEMBERS as $id => $member) {
            Member::query()->updateOrCreate(['id' => $id], [
                ...$member,
                'google_id' => "e2e-{$id}",
                'city' => 'Lages',
                'terms_accepted_at' => now(),
            ]);
            Consent::query()->create([
                'type' => ConsentType::Terms->value, 'version' => 'inicial', 'member_id' => $id, 'given_at' => now(),
            ]);
        }
    }
}
