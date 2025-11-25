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
        Schema::create('tax_obligations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_resolution_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['pio', 'porez']); // PIO (pension) or Porez (tax)
            $table->string('description'); // e.g., "DOPRINOS ZA PIO", "POREZ NA PRIHODE OD SAMOSTALNE DELATNOSTI"
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('RSD');
            $table->integer('year');
            $table->integer('month')->nullable(); // For monthly obligations
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->string('payment_code')->nullable(); // Šifra plaćanja (253 for PIO, 253 for taxes)
            $table->string('payment_recipient_account')->nullable(); // Račun primaoca
            $table->string('payment_payer_account')->nullable(); // Račun plać aoca
            $table->string('payment_model')->nullable(); // Model (97)
            $table->string('payment_reference')->nullable(); // Poziv na broj
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_obligations');
    }
};
