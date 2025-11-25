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
        Schema::create('kpo_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpo_upload_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('entry_number')->nullable();
            $table->date('date')->nullable();
            $table->string('invoice_mark')->nullable();
            $table->text('product_service_description')->nullable();
            $table->string('client_name')->nullable();
            $table->decimal('income_amount', 15, 2)->default(0);
            $table->decimal('expense_amount', 15, 2)->default(0);
            $table->string('currency')->default('RSD');
            $table->json('raw_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['kpo_upload_id', 'entry_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpo_entries');
    }
};
