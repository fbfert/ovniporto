<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The hangar and the indoor museum got their concept illustrations. Spaces created before keep a null
 * illustration; this fills it from the plan data, and leaves alone any illustration chosen in the panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (require database_path('data/place_spaces.php') as $space) {
            if ($space['concept'] === null) {
                continue;
            }
            DB::table('place_spaces')
                ->where('slug', $space['slug'])
                ->whereNull('concept_image_path')
                ->update(['concept_image_path' => $space['concept'], 'updated_at' => now()]);
        }
    }

    /** Data only: the panel may have changed the illustration since. */
    public function down(): void {}
};
