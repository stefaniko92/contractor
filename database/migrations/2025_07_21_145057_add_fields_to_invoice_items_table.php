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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->enum('type', ['service', 'product'])->default('service')->after('description');
            $table->decimal('discount_value', 10, 2)->default(0)->after('amount');
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent')->after('discount_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'discount_value', 'discount_type']);
        });
    }
};
