<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Production-safe seed. Demo data lives in the dev:seed-demo command (local only). */
    public function run(): void
    {
        $this->call([
            ContentBlockSeeder::class,
            FaqSeeder::class,
            CommunityRuleSeeder::class,
            PlaceSpaceSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
