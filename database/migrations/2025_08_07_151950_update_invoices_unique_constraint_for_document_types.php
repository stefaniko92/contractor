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
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the old unique constraint on invoice_number only
            $table->dropUnique(['invoice_number']);

            // Add new composite unique constraint on invoice_number + invoice_document_type
            $table->unique(['invoice_number', 'invoice_document_type'], 'invoices_number_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('invoices_number_type_unique');

            // Restore the old unique constraint on invoice_number only
            $table->unique('invoice_number');
        });
    }
};
