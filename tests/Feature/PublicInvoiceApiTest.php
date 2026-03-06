<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicInvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');
        Cache::flush();

        // Mock Gotenberg HTTP responses
        \Illuminate\Support\Facades\Http::fake([
            '*/forms/chromium/convert/html' => \Illuminate\Support\Facades\Http::response(
                '%PDF-1.4 fake pdf content',
                200
            ),
        ]);
    }

    public function test_successful_invoice_generation_with_new_user(): void
    {
        $payload = $this->getValidPayload();

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user_created' => true,
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // Verify company was created
        $this->assertDatabaseHas('user_companies', [
            'company_tax_id' => '123456789',
        ]);

        // Verify client was created
        $this->assertDatabaseHas('clients', [
            'company_name' => 'Kupac d.o.o.',
        ]);

        // Verify invoice was created
        $this->assertDatabaseHas('invoices', [
            'trading_place' => 'Beograd',
        ]);

        // Verify invoice items were created
        $this->assertDatabaseHas('invoice_items', [
            'title' => 'Konsultantske usluge',
        ]);
    }

    public function test_successful_invoice_generation_with_existing_user(): void
    {
        // Create existing user
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = $this->getValidPayload(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user_created' => false,
            ]);

        // Should only have one user with this email
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    public function test_rate_limit_exceeded_after_3_invoices(): void
    {
        // Remove invoice number so each request generates a new one
        $basePayload = $this->getValidPayload([
            'invoice' => array_merge($this->getValidInvoiceData(), [
                'number' => null, // Auto-generate unique numbers
                'date_issued' => now()->format('Y-m-d'),
                'date_due' => now()->addDays(30)->format('Y-m-d'),
            ]),
        ]);

        // Send 3 successful requests
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/public/generate-invoice', $basePayload);
            $response->assertStatus(200);
        }

        // 4th request should be rate limited
        $response = $this->postJson('/api/public/generate-invoice', $basePayload);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_validation_error_invalid_pib(): void
    {
        $payload = $this->getValidPayload([
            'seller' => [
                'pib' => '12345', // Too short
                'company_name' => 'Test Company',
                'address' => 'Test Address',
            ],
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'Validation error',
            ])
            ->assertJsonStructure([
                'details' => [
                    'seller.pib',
                ],
            ]);
    }

    public function test_validation_error_missing_required_fields(): void
    {
        $response = $this->postJson('/api/public/generate-invoice', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'Validation error',
            ])
            ->assertJsonStructure([
                'details' => [
                    'email',
                    'invoice_type',
                    'seller.pib',
                    'buyer.name',
                    'invoice.date_issued',
                    'items',
                ],
            ]);
    }

    public function test_validation_error_invalid_currency(): void
    {
        $payload = $this->getValidPayload([
            'invoice' => array_merge($this->getValidInvoiceData(), [
                'currency' => 'INVALID',
            ]),
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'invoice.currency',
                ],
            ]);
    }

    public function test_validation_error_empty_items_array(): void
    {
        $payload = $this->getValidPayload(['items' => []]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'items',
                ],
            ]);
    }

    public function test_validation_error_invalid_item_type(): void
    {
        $payload = $this->getValidPayload([
            'items' => [
                [
                    'title' => 'Test Item',
                    'type' => 'invalid_type',
                    'unit' => 'komad',
                    'quantity' => 1,
                    'unit_price' => 100,
                ],
            ],
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'items.0.type',
                ],
            ]);
    }

    public function test_invoice_number_auto_generation(): void
    {
        $payload = $this->getValidPayload([
            'invoice' => array_merge($this->getValidInvoiceData(), [
                'number' => null, // Should auto-generate
                'date_issued' => now()->format('Y-m-d'),
                'date_due' => now()->addDays(30)->format('Y-m-d'),
            ]),
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200);

        // Verify an invoice number was generated
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => '1/'.now()->year,
        ]);
    }

    public function test_discount_calculation_percent_type(): void
    {
        $payload = $this->getValidPayload([
            'items' => [
                [
                    'title' => 'Test Item',
                    'type' => 'usluga',
                    'unit' => 'komad',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_value' => 10, // 10% discount
                    'discount_type' => '%',
                ],
            ],
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200);

        // 100 - 10% = 90
        $this->assertDatabaseHas('invoice_items', [
            'title' => 'Test Item',
            'amount' => 90.00,
            'discount_type' => 'percent',
        ]);
    }

    public function test_discount_calculation_fixed_type(): void
    {
        $payload = $this->getValidPayload([
            'items' => [
                [
                    'title' => 'Test Item',
                    'type' => 'usluga',
                    'unit' => 'komad',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_value' => 20, // Fixed 20 currency discount
                    'discount_type' => 'currency',
                ],
            ],
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200);

        // 100 - 20 = 80
        $this->assertDatabaseHas('invoice_items', [
            'title' => 'Test Item',
            'amount' => 80.00,
            'discount_type' => 'fixed',
        ]);
    }

    public function test_domestic_invoice_type_mapping(): void
    {
        $payload = $this->getValidPayload([
            'invoice_type' => 'domaca',
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'is_domestic' => 1,
        ]);
    }

    public function test_foreign_invoice_type_mapping(): void
    {
        $payload = $this->getValidPayload([
            'invoice_type' => 'inostrana',
        ]);

        $response = $this->postJson('/api/public/generate-invoice', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'is_domestic' => 0,
        ]);
    }

    // Helper methods

    private function getValidPayload(array $overrides = []): array
    {
        $defaults = [
            'email' => 'test@example.com',
            'invoice_type' => 'domaca',
            'seller' => [
                'pib' => '123456789',
                'mb' => '12345678',
                'company_name' => 'Prodavac d.o.o.',
                'address' => 'Ulica 123',
                'city' => 'Beograd',
                'phone' => '011234567',
            ],
            'buyer' => [
                'name' => 'Kupac d.o.o.',
                'pib' => '987654321',
                'address' => 'Ulica kupca 456',
                'city' => 'Novi Sad',
            ],
            'invoice' => $this->getValidInvoiceData(),
            'items' => [
                [
                    'title' => 'Konsultantske usluge',
                    'type' => 'usluga',
                    'unit' => 'sat',
                    'quantity' => 10,
                    'unit_price' => 5000,
                    'description' => 'Konsultantske usluge za januar 2025',
                ],
            ],
        ];

        return array_merge($defaults, $overrides);
    }

    private function getValidInvoiceData(): array
    {
        return [
            'number' => '1/2025',
            'date_issued' => '2025-01-15',
            'date_due' => '2025-02-15',
            'place' => 'Beograd',
            'currency' => 'RSD',
            'note' => 'Test note',
        ];
    }
}
