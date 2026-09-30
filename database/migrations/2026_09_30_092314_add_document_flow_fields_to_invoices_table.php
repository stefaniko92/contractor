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
            $table->foreignId('source_profaktura_id')
                ->nullable()
                ->after('original_invoice_date')
                ->constrained('invoices')
                ->nullOnDelete();
            $table->text('credit_note_reason')->nullable()->after('source_profaktura_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['source_profaktura_id']);
            $table->dropColumn(['source_profaktura_id', 'credit_note_reason']);
        });
    }
};
