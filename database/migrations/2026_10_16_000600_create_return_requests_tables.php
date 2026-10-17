<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Return and withdrawal requests (CDC art. 49). Each belongs to a paid order and goes with it:
 * the order's purge deletes them, its anonymization clears name, e-mail and message.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Yearly counter behind "DEV-2026-000123", taken under a row lock like the orders'.
        Schema::create('return_request_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last')->default(0);
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('protocol', 20)->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('reason', 20);
            $table->text('message')->nullable();
            $table->boolean('within_withdrawal');
            $table->timestamp('delivered_at')->nullable();
            $table->string('status', 20)->default('received')->index();
            $table->timestamps();
        });

        Schema::create('return_request_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('actor_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_events');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('return_request_sequences');
    }
};
