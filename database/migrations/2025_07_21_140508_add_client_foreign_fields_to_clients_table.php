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
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_domestic')->default(true);
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('registration_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['is_domestic', 'city', 'country', 'vat_number', 'registration_number']);
        });
    }
};
