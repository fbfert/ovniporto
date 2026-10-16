<?php

use Illuminate\Database\Migrations\Migration;

/**
 * The model texts got the data the encarregado sent (controller, contacts, age, returns, venue).
 * Same rule as the first fill: only a text that still has blanks to fill is replaced.
 */
return new class extends Migration
{
    public function up(): void
    {
        (require __DIR__.'/2026_10_16_000100_fill_legal_model_texts.php')->up();
    }

    /** Data only: the text may have been edited in the panel since. */
    public function down(): void {}
};
