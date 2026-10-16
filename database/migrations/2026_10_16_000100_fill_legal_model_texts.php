<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Model texts of the privacy policy and the terms, for the encarregado to review on the site.
 * Production never runs the seeders, so this writes them while a block is still empty or
 * unwritten; a text already written in the panel stays. The draft notice stays on.
 */
return new class extends Migration
{
    /** Texts nobody wrote yet: the topic skeleton or a model text with blanks to fill. */
    private const UNWRITTEN_MARKS = ['_Tópico a ser redigido pelo encarregado._', '**[A preencher'];

    private const TEXTS = [
        'privacy' => 'data/politica-privacidade-modelo.md',
        'terms' => 'data/termos-uso-modelo.md',
    ];

    public function up(): void
    {
        foreach (self::TEXTS as $kind => $file) {
            $this->fill($kind, (string) file_get_contents(database_path($file)));
        }
    }

    /** Data only: the text may have been edited in the panel since. */
    public function down(): void {}

    private function fill(string $kind, string $text): void
    {
        $existing = DB::table('content_blocks')->where('key', "{$kind}_body")->first();
        if ($existing !== null && ! blank($existing->value) && ! Str::contains($existing->value, self::UNWRITTEN_MARKS)) {
            return;
        }

        $this->put("{$kind}_body", $text);
        $this->put("{$kind}_updated_at", now()->toDateString());
    }

    private function put(string $key, string $value): void
    {
        $block = DB::table('content_blocks')->where('key', $key);
        if ($block->exists()) {
            $block->update(['value' => $value, 'updated_at' => now()]);

            return;
        }

        DB::table('content_blocks')->insert(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
    }
};
