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
        Schema::table('clients', function (Blueprint $table) {
            // Track if client exists in eFaktura system
            $table->boolean('efaktura_verified')->default(false)->after('notes');
            $table->timestamp('efaktura_verified_at')->nullable()->after('efaktura_verified');
            $table->string('efaktura_status')->nullable()->after('efaktura_verified_at'); // active, not_found, error
            $table->text('efaktura_verification_error')->nullable()->after('efaktura_status');

            // Index for querying unverified clients
            $table->index('efaktura_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['efaktura_verified']);
            $table->dropColumn([
                'efaktura_verified',
                'efaktura_verified_at',
                'efaktura_status',
                'efaktura_verification_error',
            ]);
        });
    }
};
