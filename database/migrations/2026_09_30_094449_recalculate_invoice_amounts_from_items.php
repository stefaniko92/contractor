<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('invoices')
            ->where('amount', 0)
            ->orderBy('id')
            ->each(function (object $invoice): void {
                $amount = DB::table('invoice_items')
                    ->where('invoice_id', $invoice->id)
                    ->sum('amount');

                if ((float) $amount === 0.0) {
                    return;
                }

                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update([
                        'amount' => $amount,
                        'updated_at' => now(),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
