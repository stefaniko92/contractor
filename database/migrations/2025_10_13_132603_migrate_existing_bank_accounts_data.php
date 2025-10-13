<?php

use App\Models\BankAccount;
use App\Models\UserCompany;
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
        // Migrate existing foreign account data to bank_accounts table
        UserCompany::whereNotNull('company_foreign_account_number')
            ->orWhereNotNull('company_foreign_account_bank')
            ->each(function ($userCompany) {
                if ($userCompany->company_foreign_account_number || $userCompany->company_foreign_account_bank) {
                    BankAccount::create([
                        'user_company_id' => $userCompany->id,
                        'account_number' => $userCompany->company_foreign_account_number ?? 'N/A',
                        'bank_name' => $userCompany->company_foreign_account_bank ?? 'N/A',
                        'account_type' => 'foreign',
                        'currency' => 'EUR', // Default to EUR for existing foreign accounts
                        'is_primary' => false,
                    ]);
                }
            });

        // Drop the old columns from user_companies table
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropColumn(['company_foreign_account_number', 'company_foreign_account_bank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the old columns
        Schema::table('user_companies', function (Blueprint $table) {
            $table->string('company_foreign_account_number')->nullable();
            $table->string('company_foreign_account_bank')->nullable();
        });

        // Migrate data back (if needed for rollback)
        BankAccount::where('account_type', 'foreign')->each(function ($bankAccount) {
            $userCompany = $bankAccount->userCompany;
            if ($userCompany && !$userCompany->company_foreign_account_number) {
                $userCompany->update([
                    'company_foreign_account_number' => $bankAccount->account_number,
                    'company_foreign_account_bank' => $bankAccount->bank_name,
                ]);
            }
        });

        // Delete bank accounts
        BankAccount::truncate();
    }
};
