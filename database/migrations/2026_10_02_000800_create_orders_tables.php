<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Yearly counter behind "OVP-2026-000123", taken under a row lock.
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last')->default(0);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            // The visitor cart this order came from, emptied once paid.
            $table->uuid('cart_token')->nullable();
            $table->string('status', 20)->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 20)->nullable();
            // Encrypted cast: never readable in the database.
            $table->text('customer_cpf')->nullable();
            $table->json('address')->nullable();
            $table->boolean('pickup')->default(false);
            $table->string('shipping_option_id', 60)->nullable();
            $table->string('shipping_carrier', 60)->nullable();
            $table->string('shipping_service', 60)->nullable();
            $table->unsignedSmallInteger('shipping_days')->nullable();
            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('shipping_cents');
            $table->unsignedInteger('total_cents');
            $table->string('payment_order_id', 60)->nullable()->unique();
            $table->string('payment_capture_id', 60)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('shipment_id', 60)->nullable();
            $table->string('tracking_code', 60)->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            // Copies at the time of purchase: later price or name changes never touch a placed order.
            $table->string('product_name');
            $table->string('variant_name');
            $table->string('sku', 60);
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('line_cents');
            $table->boolean('made_to_order')->default(false);
            $table->unsignedSmallInteger('production_days')->default(0);
            $table->unsignedInteger('weight_grams')->default(0);
        });

        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('actor', 20);
            $table->foreignId('actor_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_sequences');
    }
};
