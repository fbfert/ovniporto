<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Production never runs the seeders, so /o-lugar showed no spaces. This adds the nine spaces of the plan
 * that are missing and fills an empty description; whatever the panel already edited stays as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (require database_path('data/place_spaces.php') as $order => $space) {
            $existing = DB::table('place_spaces')->where('slug', $space['slug'])->first();

            if ($existing === null) {
                DB::table('place_spaces')->insert([
                    'slug' => $space['slug'],
                    'name' => $space['name'],
                    'role' => $space['role'],
                    'description' => $space['description'],
                    'phase' => $space['phase'],
                    'status' => 'planning',
                    'sort_order' => $order,
                    'concept_image_path' => $space['concept'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif (blank($existing->description)) {
                DB::table('place_spaces')->where('id', $existing->id)->update(['description' => $space['description'], 'updated_at' => now()]);
            }
        }
    }

    /** Data only: the rows stay, since the panel may have edited them since. */
    public function down(): void {}
};
