<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    protected $fillable = [
        'user_company_id',
        'bank_id',
        'account_number',
        'iban',
        'bank_name',
        'swift',
        'account_type',
        'currency',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function userCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public static function getCurrencies(): array
    {
        return [
            'RSD' => 'RSD - Serbian Dinar',
            'EUR' => 'EUR - Euro',
            'USD' => 'USD - US Dollar',
            'CHF' => 'CHF - Swiss Franc',
            'GBP' => 'GBP - British Pound',
            'AUD' => 'AUD - Australian Dollar',
            'CAD' => 'CAD - Canadian Dollar',
            'NOK' => 'NOK - Norwegian Krone',
            'RUB' => 'RUB - Russian Ruble',
            'CNY' => 'CNY - Chinese Yuan',
            'AED' => 'AED - UAE Dirham',
            'BAM' => 'BAM - Bosnia-Herzegovina Mark',
            'BGN' => 'BGN - Bulgarian Lev',
            'BYN' => 'BYN - Belarusian Ruble',
            'CZK' => 'CZK - Czech Koruna',
            'DKK' => 'DKK - Danish Krone',
            'HUF' => 'HUF - Hungarian Forint',
            'INR' => 'INR - Indian Rupee',
            'JPY' => 'JPY - Japanese Yen',
            'KWD' => 'KWD - Kuwaiti Dinar',
            'MKD' => 'MKD - Macedonian Denar',
            'PLN' => 'PLN - Polish Zloty',
            'RON' => 'RON - Romanian Leu',
            'TRY' => 'TRY - Turkish Lira',
        ];
    }
}
