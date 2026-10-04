<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Set when the customer's account is deleted: the anonymized order is purged after this date.
            $table->timestamp('retention_until')->nullable()->index()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('retention_until');
        });
    }
};
