<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('region_partners', function (Blueprint $table) {
            // Private disk: the partner's written consent, never served publicly.
            $table->string('consent_proof_path')->nullable()->after('consent_given_at');
        });
    }

    public function down(): void
    {
        Schema::table('region_partners', function (Blueprint $table) {
            $table->dropColumn('consent_proof_path');
        });
    }
};
