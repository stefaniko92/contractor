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
        Schema::create('user_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('company_full_name')->nullable();
            $table->string('company_tax_id')->nullable();
            $table->string('company_registry_number')->nullable();
            $table->string('company_activity_code')->nullable();
            $table->text('company_activity_desc')->nullable();
            $table->date('company_registration_date')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('company_status')->nullable();
            $table->string('company_municipality')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_address_number')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->boolean('show_email_on_invoice')->default(true);
            $table->string('company_foreign_account_number')->nullable();
            $table->string('company_foreign_account_bank')->nullable();
            $table->string('company_logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_companies');
    }
};
