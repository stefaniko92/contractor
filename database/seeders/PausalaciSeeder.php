<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CompanyOwner;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\Obligation;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Database\Seeder;

class PausalaciSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user (pausalac)
        $user = User::firstOrCreate([
            'email' => 'pausalac@test.com',
        ], [
            'name' => 'Marko Petrović',
            'password' => bcrypt('password'),
            'company_name' => 'Marko Petrović PR',
            'tax_id' => '123456789',
            'address' => 'Knez Mihailova 42, 11000 Beograd',
            'phone' => '+381 11 123 4567',
            'default_currency' => 'RSD',
        ]);

        // Also add data for any existing admin users
        $allUsers = User::all();

        foreach ($allUsers as $currentUser) {
            $this->createSampleDataForUser($currentUser);
        }
    }

    private function createSampleDataForUser(User $user): void
    {
        // Create user company profile
        $userCompany = UserCompany::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'company_name' => $user->company_name ?? 'Marko Petrović PR',
            'company_full_name' => ($user->company_name ?? 'Marko Petrović PR').' - Preduzetnik',
            'company_tax_id' => $user->tax_id ?? '123456789',
            'company_registry_number' => '62'.str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'company_activity_code' => '6201',
            'company_activity_desc' => 'Računarske programske delatnosti',
            'company_registration_date' => now()->subYears(rand(1, 5)),
            'company_city' => 'Beograd',
            'company_postal_code' => '11000',
            'company_status' => 'Aktivan',
            'company_municipality' => 'Stari Grad',
            'company_address' => $user->address ?? 'Knez Mihailova 42',
            'company_address_number' => '42',
            'company_phone' => $user->phone ?? '+381 11 123 4567',
            'company_email' => $user->email,
            'show_email_on_invoice' => true,
        ]);

        // Create company owner profile
        CompanyOwner::firstOrCreate([
            'user_company_id' => $userCompany->id,
        ], [
            'first_name' => explode(' ', $user->name)[0] ?? 'Marko',
            'last_name' => explode(' ', $user->name)[1] ?? 'Petrović',
            'parent_name' => 'Miloš',
            'nationality' => 'Srpska',
            'personal_id_number' => '1234567890123',
            'education_level' => 'Visoka stručna sprema',
            'gender' => 'male',
            'city' => 'Beograd',
            'municipality' => 'Stari Grad',
            'address' => $user->address ?? 'Knez Mihailova 42',
            'address_number' => '42',
            'email' => $user->email,
        ]);

        // Create some sample clients
        $clients = [
            [
                'company_name' => 'ABC d.o.o.',
                'tax_id' => '987654321',
                'address' => 'Nemanjina 15, 11000 Beograd',
                'email' => 'office@abc.rs',
                'phone' => '+381 11 987 6543',
                'notes' => 'Redovan klijent, plaćanje na 30 dana',
            ],
            [
                'company_name' => 'XYZ Solutions',
                'tax_id' => '456789123',
                'address' => 'Terazije 25, 11000 Beograd',
                'email' => 'info@xyz.rs',
                'phone' => '+381 11 456 7890',
                'notes' => 'Novi klijent, potrebno je pratiti plaćanja',
            ],
            [
                'company_name' => 'Tech Startup',
                'tax_id' => '789123456',
                'address' => 'Savska 10, 11000 Beograd',
                'email' => 'contact@techstartup.rs',
                'phone' => '+381 11 789 1234',
                'notes' => 'Startup kompanija, mesečni ugovori',
            ],
        ];

        foreach ($clients as $clientData) {
            $clientData['user_id'] = $user->id;
            $client = Client::firstOrCreate([
                'user_id' => $user->id,
                'company_name' => $clientData['company_name'],
            ], $clientData);

            // Create some sample invoices
            $invoices = [
                [
                    'invoice_number' => 'INV-'.date('Y').'-'.$user->id.'-'.$client->id.'-'.str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT),
                    'amount' => rand(50000, 500000),
                    'description' => 'Usluge konsaltinga i razvoja softvera',
                    'currency' => 'RSD',
                    'issue_date' => now()->subDays(rand(1, 30)),
                    'due_date' => now()->addDays(rand(15, 45)),
                    'status' => rand(0, 1) ? 'paid' : 'unpaid',
                ],
                [
                    'invoice_number' => 'INV-'.date('Y').'-'.$user->id.'-'.$client->id.'-'.str_pad(rand(100, 199), 2, '0', STR_PAD_LEFT),
                    'amount' => rand(75000, 300000),
                    'description' => 'Mesečne usluge održavanja sistema',
                    'currency' => 'RSD',
                    'issue_date' => now()->subDays(rand(5, 20)),
                    'due_date' => now()->addDays(rand(10, 30)),
                    'status' => 'unpaid',
                ],
            ];

            foreach ($invoices as $invoiceData) {
                $invoice = Invoice::firstOrCreate([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'invoice_number' => $invoiceData['invoice_number'],
                ], $invoiceData);

                // Create income record if invoice is paid
                if ($invoice->status === 'paid') {
                    Income::firstOrCreate([
                        'user_id' => $user->id,
                        'invoice_id' => $invoice->id,
                    ], [
                        'amount' => $invoice->amount,
                        'date' => $invoice->issue_date->addDays(rand(1, 10)),
                        'description' => 'Plaćanje za fakturu '.$invoice->invoice_number,
                    ]);
                }
            }
        }

        // Create some sample obligations
        $obligations = [
            [
                'year' => now()->year,
                'month' => now()->month,
                'type' => 'tax',
                'amount' => 25000,
                'status' => 'pending',
            ],
            [
                'year' => now()->year,
                'month' => now()->month,
                'type' => 'pension',
                'amount' => 35000,
                'status' => 'pending',
            ],
            [
                'year' => now()->year,
                'month' => now()->month,
                'type' => 'health',
                'amount' => 15000,
                'status' => 'pending',
            ],
        ];

        foreach ($obligations as $obligationData) {
            Obligation::firstOrCreate([
                'user_id' => $user->id,
                'year' => $obligationData['year'],
                'month' => $obligationData['month'],
                'type' => $obligationData['type'],
            ], $obligationData);
        }

        // Create sample user company profile
        $userCompany = UserCompany::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'company_name' => $user->company_name ?? 'Moja kompanija d.o.o.',
            'company_full_name' => 'Moja kompanija za konsalting i razvoj softvera d.o.o.',
            'company_tax_id' => $user->tax_id ?? '123456789',
            'company_registry_number' => '20123456',
            'company_activity_code' => '62.01',
            'company_activity_desc' => 'Programiranje i konsalting u oblasti informacionih tehnologija',
            'company_registration_date' => now()->subYears(2),
            'company_city' => 'Beograd',
            'company_postal_code' => '11000',
            'company_status' => 'Aktivna',
            'company_municipality' => 'Stari grad',
            'company_address' => $user->address ?? 'Knez Mihailova 42',
            'company_address_number' => '42',
            'company_phone' => $user->phone ?? '+381 11 123 4567',
            'company_email' => $user->email,
            'show_email_on_invoice' => true,
            'company_foreign_account_number' => null,
            'company_foreign_account_bank' => null,
            'company_logo_path' => null,
        ]);

        // Create sample company owner profile
        CompanyOwner::firstOrCreate([
            'user_company_id' => $userCompany->id,
        ], [
            'first_name' => 'Marko',
            'last_name' => 'Petrović',
            'parent_name' => 'Miloš',
            'nationality' => 'Srpska',
            'personal_id_number' => '1234567890123',
            'education_level' => 'Visoko obrazovanje',
            'gender' => 'male',
            'city' => 'Beograd',
            'municipality' => 'Stari grad',
            'address' => 'Knez Mihailova 42',
            'address_number' => '42',
            'email' => $user->email,
        ]);
    }
}
