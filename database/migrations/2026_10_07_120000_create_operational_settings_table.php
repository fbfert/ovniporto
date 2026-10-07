<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** /painel/coordenadas: one row per setting the admin saved; no row means the .env value. */
    public function up(): void
    {
        Schema::create('operational_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            // Secrets (the SMTP password) are encrypted with APP_KEY before they get here.
            $table->text('value');
            $table->boolean('is_secret')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_settings');
    }
};
