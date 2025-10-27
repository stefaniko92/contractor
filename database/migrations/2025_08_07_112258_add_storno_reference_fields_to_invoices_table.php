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
            // Add fields to track storno/reversal invoices
            $table->boolean('is_storno')->default(false)->after('invoice_document_type');
            $table->unsignedBigInteger('original_invoice_id')->nullable()->after('is_storno');
            $table->string('original_invoice_number')->nullable()->after('original_invoice_id');
            $table->date('original_invoice_date')->nullable()->after('original_invoice_number');

            // Add foreign key constraint
            $table->foreign('original_invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['original_invoice_id']);

            // Drop columns
            $table->dropColumn(['is_storno', 'original_invoice_id', 'original_invoice_number', 'original_invoice_date']);
        });
    }
};
