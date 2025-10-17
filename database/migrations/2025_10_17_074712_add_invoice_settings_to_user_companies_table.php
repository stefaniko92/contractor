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
        Schema::table('user_companies', function (Blueprint $table) {
            $table->text('invoice_note_domestic')->nullable()->after('company_logo_path');
            $table->text('invoice_note_foreign')->nullable()->after('invoice_note_domestic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropColumn(['invoice_note_domestic', 'invoice_note_foreign']);
        });
    }
};
