<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, temporarily change the column to VARCHAR to avoid enum constraint
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
        
        // Update existing data to map old values to new values
        DB::table('invoices')->where('status', 'paid')->update(['status' => 'charged']);
        DB::table('invoices')->where('status', 'unpaid')->update(['status' => 'uncharged']);
        
        // Now change to the new enum with updated values
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['sent', 'issued', 'in_preparation', 'charged', 'uncharged', 'storned'])
                ->default('in_preparation')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['paid', 'unpaid'])
                ->default('unpaid')
                ->change();
        });
    }
};
