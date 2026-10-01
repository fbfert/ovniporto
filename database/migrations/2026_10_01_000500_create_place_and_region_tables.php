<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('role');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('phase');
            $table->string('status', 20)->default('planning');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('concept_image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('region_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 20);
            $table->string('short_description')->nullable();
            $table->string('city', 80);
            $table->string('address')->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('instagram', 60)->nullable();
            $table->string('website')->nullable();
            $table->string('cover_path')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('consent_given_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_partners');
        Schema::dropIfExists('place_spaces');
    }
};
