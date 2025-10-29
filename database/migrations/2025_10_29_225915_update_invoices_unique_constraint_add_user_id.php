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
            // Drop the old unique constraint on (invoice_number, invoice_document_type)
            $table->dropUnique('invoices_number_type_unique');

            // Add new composite unique constraint on (user_id, invoice_number, invoice_document_type)
            // This allows different users to have the same invoice numbers
            $table->unique(['user_id', 'invoice_number', 'invoice_document_type'], 'invoices_user_number_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('invoices_user_number_type_unique');

            // Restore the old unique constraint
            $table->unique(['invoice_number', 'invoice_document_type'], 'invoices_number_type_unique');
        });
    }
};
