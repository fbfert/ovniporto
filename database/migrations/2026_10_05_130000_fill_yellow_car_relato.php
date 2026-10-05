<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Julean sent the relato of the yellow car. Production never runs the seeders, so this writes it
 * into the editable block while the block is still empty; a text already written in the panel stays.
 */
return new class extends Migration
{
    private const KEY = 'legend_body';

    public function up(): void
    {
        $relato = (string) file_get_contents(database_path('data/relato-carro-amarelo.md'));
        $existing = DB::table('content_blocks')->where('key', self::KEY)->first();

        if ($existing === null) {
            DB::table('content_blocks')->insert(['key' => self::KEY, 'value' => $relato, 'created_at' => now(), 'updated_at' => now()]);
        } elseif (blank($existing->value)) {
            DB::table('content_blocks')->where('id', $existing->id)->update(['value' => $relato, 'updated_at' => now()]);
        }
    }

    /** Data only: the text may have been edited in the panel since. */
    public function down(): void {}
};
