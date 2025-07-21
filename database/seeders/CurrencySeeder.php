<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => 'РСД', 'exchange_rate_to_rsd' => 1.0000, 'sort_order' => 1],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate_to_rsd' => 117.1743, 'sort_order' => 2],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_rsd' => 100.3548, 'sort_order' => 3],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'exchange_rate_to_rsd' => 125.8180, 'sort_order' => 4],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate_to_rsd' => 135.1803, 'sort_order' => 5],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'exchange_rate_to_rsd' => 65.8320, 'sort_order' => 6],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'exchange_rate_to_rsd' => 73.2568, 'sort_order' => 7],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'exchange_rate_to_rsd' => 9.8737, 'sort_order' => 8],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'exchange_rate_to_rsd' => 1.2871, 'sort_order' => 9],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'exchange_rate_to_rsd' => 13.9956, 'sort_order' => 10],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'exchange_rate_to_rsd' => 27.3204, 'sort_order' => 11],
            ['code' => 'BAM', 'name' => 'Bosnia and Herzegovina Convertible Mark', 'symbol' => 'КМ', 'exchange_rate_to_rsd' => 59.9103, 'sort_order' => 12],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev', 'symbol' => 'лв', 'exchange_rate_to_rsd' => 59.9103, 'sort_order' => 13],
            ['code' => 'BYN', 'name' => 'Belarusian Ruble', 'symbol' => 'Br', 'exchange_rate_to_rsd' => 30.6042, 'sort_order' => 14],
            ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'exchange_rate_to_rsd' => 4.7464, 'sort_order' => 15],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'exchange_rate_to_rsd' => 15.6996, 'sort_order' => 16],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'exchange_rate_to_rsd' => 29.2665, 'sort_order' => 17],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'exchange_rate_to_rsd' => 1.1674, 'sort_order' => 18],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'exchange_rate_to_rsd' => 68.0771, 'sort_order' => 19],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'exchange_rate_to_rsd' => 328.3113, 'sort_order' => 20],
            ['code' => 'MKD', 'name' => 'North Macedonian Denar', 'symbol' => 'ден', 'exchange_rate_to_rsd' => 1.8960, 'sort_order' => 21],
            ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'exchange_rate_to_rsd' => 27.4214, 'sort_order' => 22],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'lei', 'exchange_rate_to_rsd' => 23.0626, 'sort_order' => 23],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺', 'exchange_rate_to_rsd' => 2.4941, 'sort_order' => 24],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
