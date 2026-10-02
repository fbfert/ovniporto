<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('action', 60);
            $table->string('subject_type', 40);
            $table->unsignedBigInteger('subject_id');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::table('sightings', function (Blueprint $table) {
            // Panel-only: the town reverse-geocoded from the point, never shown publicly.
            $table->string('approx_city', 80)->nullable()->after('place_label');
            $table->timestamp('submitted_at')->nullable()->after('consent_given_at');
        });
    }

    public function down(): void
    {
        Schema::table('sightings', function (Blueprint $table) {
            $table->dropColumn(['approx_city', 'submitted_at']);
        });
        Schema::dropIfExists('audit_logs');
    }
};
