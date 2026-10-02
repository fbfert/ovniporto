<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_photos', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('alt');
            $table->string('caption')->nullable();
            $table->date('taken_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('construction_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->text('body');
            $table->string('cover_path')->nullable();
            $table->string('cover_alt')->nullable();
            $table->unsignedTinyInteger('phase')->default(1);
            $table->json('gallery')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        // Single row: the campaign is one thing. Everything but the status starts empty.
        Schema::create('campaign_settings', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('planning');
            $table->unsignedBigInteger('goal_cents')->nullable();
            $table->unsignedBigInteger('raised_cents')->nullable();
            $table->string('crowdfunding_url')->nullable();
            $table->decimal('store_share_percent', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('supporters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('amount_cents')->nullable();
            $table->string('reward')->nullable();
            $table->boolean('publish_name')->default(false);
            $table->timestamp('supported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tier')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('supporters');
        Schema::dropIfExists('campaign_settings');
        Schema::dropIfExists('construction_posts');
        Schema::dropIfExists('site_photos');
    }
};
