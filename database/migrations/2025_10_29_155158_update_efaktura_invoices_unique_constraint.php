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
        Schema::table('efaktura_invoices', function (Blueprint $table) {
            // Drop the unique constraint on sef_invoice_id
            $table->dropUnique(['sef_invoice_id']);

            // Add unique constraint on invoice_id instead
            // (one invoice should only have one efaktura record)
            $table->unique('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('efaktura_invoices', function (Blueprint $table) {
            // Drop the unique constraint on invoice_id
            $table->dropUnique(['invoice_id']);

            // Restore the unique constraint on sef_invoice_id
            $table->unique('sef_invoice_id');
        });
    }
};
