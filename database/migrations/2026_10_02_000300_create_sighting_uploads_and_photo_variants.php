<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step one of the two-step upload: files land in a private temporary area
        // and get an id; the report references the ids. Orphans are pruned after 24 h.
        Schema::create('sighting_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime', 40);
            $table->unsignedInteger('size');
            $table->timestamp('created_at')->index();
        });

        Schema::table('sighting_photos', function (Blueprint $table) {
            // Widths of the WebP variants written by ProcessSightingPhoto; null while unprocessed.
            $table->json('variants')->nullable()->after('height');
            $table->timestamp('processed_at')->nullable()->after('variants');
        });
    }

    public function down(): void
    {
        Schema::table('sighting_photos', function (Blueprint $table) {
            $table->dropColumn(['variants', 'processed_at']);
        });
        Schema::dropIfExists('sighting_uploads');
    }
};
