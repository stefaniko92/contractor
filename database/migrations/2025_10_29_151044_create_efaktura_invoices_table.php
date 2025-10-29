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
        Schema::create('efaktura_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // eFaktura system identifiers
            $table->string('sef_invoice_id')->nullable()->unique(); // ID from eFaktura system
            $table->string('sef_invoice_number')->nullable(); // Invoice number in eFaktura
            $table->string('sef_request_id')->nullable(); // Our request ID for tracking

            // Status tracking
            $table->string('status')->default('draft'); // draft, sent, delivered, accepted, rejected, cancelled
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Response data from eFaktura
            $table->json('sef_response')->nullable(); // Full response from eFaktura
            $table->json('status_history')->nullable(); // Array of status changes with timestamps

            // Webhook data
            $table->timestamp('last_webhook_at')->nullable();
            $table->json('last_webhook_payload')->nullable();

            // Error tracking
            $table->text('last_error')->nullable();
            $table->timestamp('last_error_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('sent_at');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('efaktura_invoices');
    }
};
