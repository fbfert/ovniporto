<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A withdrawn consent keeps its record and gains the date it was withdrawn. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consents', function (Blueprint $table) {
            $table->timestamp('withdrawn_at')->nullable()->after('given_at');
        });
    }

    public function down(): void
    {
        Schema::table('consents', function (Blueprint $table) {
            $table->dropColumn('withdrawn_at');
        });
    }
};
