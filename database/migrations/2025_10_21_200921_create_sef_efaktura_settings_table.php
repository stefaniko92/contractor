<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sef_efaktura_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('api_key')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('default_vat_exemption')->default('PDV-RS-33'); // Podrazumevano izuzeće od PDV-a
            $table->string('default_vat_category')->default('SS'); // Podrazumevana PDV kategorija (hardcoded SS)
            $table->text('webhook_url')->nullable(); // Our webhook URL for this user
            $table->timestamp('last_webhook_test')->nullable();
            $table->json('integration_data')->nullable(); // Store any additional metadata
            $table->timestamps();
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sef_efaktura_settings');
    }
};
