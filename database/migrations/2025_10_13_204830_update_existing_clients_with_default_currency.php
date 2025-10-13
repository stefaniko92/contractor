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
        // Update existing clients without a currency to have default values
        // Domestic clients get RSD, foreign clients get EUR
        \DB::table('clients')
            ->whereNull('currency')
            ->where('is_domestic', true)
            ->update(['currency' => 'RSD']);

        \DB::table('clients')
            ->whereNull('currency')
            ->where('is_domestic', false)
            ->update(['currency' => 'EUR']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data migration
    }
};
