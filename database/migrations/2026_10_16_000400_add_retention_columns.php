<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data retention: the last access of each member (with the notice and alert of the inactivity
 * cycle), anonymized members (no name, e-mail or Google link; the unique indexes accept many nulls)
 * and paid orders anonymized once the tax retention ends.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('google_id')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->timestamp('last_seen_at')->nullable()->index()->after('terms_accepted_at');
            $table->timestamp('inactivity_notice_sent_at')->nullable()->after('last_seen_at');
            $table->timestamp('inactivity_alerted_at')->nullable()->after('inactivity_notice_sent_at');
            $table->timestamp('anonymized_at')->nullable()->after('inactivity_alerted_at');
        });

        // updated_at is never older than the last real sign-in: the backfill only delays anonymization.
        DB::table('members')->update(['last_seen_at' => DB::raw('updated_at')]);

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('retention_until');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn(['last_seen_at', 'inactivity_notice_sent_at', 'inactivity_alerted_at', 'anonymized_at']);
        });
        // Nullability is not reverted: anonymized members would break the NOT NULL columns.
    }
};
