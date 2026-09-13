<?php

namespace App\Support;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Support\HtmlString;

class InvoiceParticipantInformation
{
    public static function forUser(User $user, ?BankAccount $bankAccount = null): HtmlString
    {
        $company = $user->userCompany;

        if ($company === null) {
            return new HtmlString('<div class="text-gray-500">Unesite podatke o kompaniji pre kreiranja fakture.</div>');
        }

        $address = trim(implode(' ', array_filter([
            $company->company_address,
            $company->company_address_number,
        ])));
        $city = trim(implode(' ', array_filter([
            $company->company_postal_code,
            $company->company_city,
        ])));

        $lines = array_filter([
            $company->company_name,
            $company->company_full_name,
            $address,
            $city,
            filled($company->company_email ?? $user->email) ? 'E-mail: '.($company->company_email ?? $user->email) : null,
            filled($company->company_tax_id) ? 'PIB: '.$company->company_tax_id : null,
            filled($company->company_registry_number) ? 'MB: '.$company->company_registry_number : null,
            $bankAccount?->account_type === 'foreign' && filled($bankAccount->swift) ? 'SWIFT: '.$bankAccount->swift : null,
            $bankAccount?->account_type === 'foreign' && filled($bankAccount->iban) ? 'IBAN: '.$bankAccount->iban : null,
        ]);

        return new HtmlString('<div class="space-y-1">'.implode('', array_map(
            fn (string $line): string => '<div>'.e($line).'</div>',
            $lines,
        )).'</div>');
    }
}
