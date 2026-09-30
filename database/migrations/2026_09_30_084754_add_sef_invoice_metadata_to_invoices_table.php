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
            $table->date('delivery_date')->nullable()->after('issue_date');
            $table->string('order_reference')->nullable()->after('trading_place');
            $table->string('contract_reference')->nullable()->after('order_reference');
            $table->string('lot_reference')->nullable()->after('contract_reference');
            $table->date('invoice_period_start_date')->nullable()->after('delivery_date');
            $table->date('invoice_period_end_date')->nullable()->after('invoice_period_start_date');
            $table->string('invoice_period_description_code')->nullable()->after('invoice_period_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_date',
                'order_reference',
                'contract_reference',
                'lot_reference',
                'invoice_period_start_date',
                'invoice_period_end_date',
                'invoice_period_description_code',
            ]);
        });
    }
};
