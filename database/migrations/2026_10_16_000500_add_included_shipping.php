<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Products whose shipping the OVNIPORTO pays. Each order item keeps the flag as it was at purchase,
 * and the order keeps the quoted price of its option next to what the buyer was charged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('shipping_included')->default(false)->after('weight_grams');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('shipping_included')->default(false)->after('weight_grams');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('shipping_cost_cents')->default(0)->after('shipping_cents');
        });

        // Before this, the buyer always paid the whole quote.
        DB::table('orders')->update(['shipping_cost_cents' => DB::raw('shipping_cents')]);
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('shipping_cost_cents'));
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('shipping_included'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('shipping_included'));
    }
};
