<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sightings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->text('description');
            $table->date('observed_date');
            $table->string('observed_time_kind', 10)->default('range');
            $table->string('observed_time_range', 20)->nullable();
            $table->time('observed_time')->nullable();
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->string('place_label', 80)->nullable();
            $table->string('gaze_direction', 2)->nullable();
            $table->string('public_nickname', 40);
            $table->timestamp('consent_given_at');
            $table->string('status', 20)->default('pending')->index();
            $table->text('moderation_note')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('sighting_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sighting_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sighting_photos');
        Schema::dropIfExists('sightings');
    }
};
