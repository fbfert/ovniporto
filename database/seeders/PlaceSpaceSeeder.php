<?php

namespace Database\Seeders;

use App\Models\PlaceSpace;
use Illuminate\Database\Seeder;

class PlaceSpaceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require database_path('data/place_spaces.php') as $order => $space) {
            PlaceSpace::query()->firstOrCreate(['slug' => $space['slug']], [
                'name' => $space['name'],
                'role' => $space['role'],
                'description' => $space['description'],
                'phase' => $space['phase'],
                'status' => 'planning',
                'sort_order' => $order,
                'concept_image_path' => $space['concept'],
            ]);
        }
    }
}
