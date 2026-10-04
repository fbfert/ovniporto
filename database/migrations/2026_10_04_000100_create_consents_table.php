<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->string('version', 32);
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('subject', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('given_at');
            $table->index(['member_id', 'type', 'given_at']);
            $table->index('email');
        });

        // Members who accepted before the ledger existed: their acceptance enters as the initial version.
        DB::table('members')->whereNotNull('terms_accepted_at')->orderBy('id')->each(function (object $member) {
            DB::table('consents')->insert([
                'type' => 'termos',
                'version' => 'inicial',
                'member_id' => $member->id,
                'given_at' => $member->terms_accepted_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
