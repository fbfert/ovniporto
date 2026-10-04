<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sighting_photos', function (Blueprint $table) {
            // Processed with AVIF siblings and the 20 px placeholder (photos processed before only have WebP).
            $table->boolean('avif')->default(false)->after('variants');
        });
    }

    public function down(): void
    {
        Schema::table('sighting_photos', function (Blueprint $table) {
            $table->dropColumn('avif');
        });
    }
};
