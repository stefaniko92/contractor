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
        Schema::create('invoice_advance_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('advance_invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('applied_amount', 15, 2);
            $table->timestamps();

            $table->unique(['invoice_id', 'advance_invoice_id'], 'inv_adv_app_invoice_advance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_advance_applications');
    }
};
