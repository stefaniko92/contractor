<?php

namespace Tests\Feature;

use App\Models\SefEfakturaSetting;
use App\Models\User;
use App\Services\SefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SefServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_returns_configuration_error_when_sef_is_not_enabled()
    {
        // Create SEF settings but keep them disabled
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => false,
            'api_key' => 'test-api-key',
        ]);

        $service = SefService::forUser($this->user->id);

        $this->assertFalse($service->isConfigured());
        $this->assertFalse($service->isEnabled());

        $status = $service->getAvailabilityStatus();
        $this->assertFalse($status['available']);
        $this->assertEquals('disabled', $status['type']);
        $this->assertStringContainsString('onemogućena', $status['message']);
    }

    /** @test */
    public function it_returns_configuration_error_when_api_key_is_missing()
    {
        // Create SEF settings enabled but without API key
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => null,
        ]);

        $service = SefService::forUser($this->user->id);

        $this->assertFalse($service->isConfigured());
        $this->assertTrue($service->isEnabled());

        $status = $service->getAvailabilityStatus();
        $this->assertFalse($status['available']);
        $this->assertEquals('missing_api_key', $status['type']);
        $this->assertStringContainsString('API ključ nije postavljen', $status['message']);
    }

    /** @test */
    public function it_returns_available_when_properly_configured()
    {
        // Create properly configured SEF settings
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key-12345',
            'default_vat_exemption' => 'PDV-RS-33',
            'default_vat_category' => 'SS',
        ]);

        $service = SefService::forUser($this->user->id);

        $this->assertTrue($service->isConfigured());
        $this->assertTrue($service->isEnabled());

        $status = $service->getAvailabilityStatus();
        $this->assertTrue($status['available']);
        $this->assertEquals('available', $status['type']);
        $this->assertStringContainsString('spremna za korišćenje', $status['message']);

        // Test that defaults are accessible
        $this->assertEquals('PDV-RS-33', $service->getDefaultVatExemption());
        $this->assertEquals('SS', $service->getDefaultVatCategory());
    }

    /** @test */
    public function it_returns_settings_not_found_when_no_settings_exist()
    {
        $service = SefService::forUser($this->user->id);

        $this->assertFalse($service->isConfigured());
        $this->assertFalse($service->isEnabled());
        $this->assertNull($service->getSefSettings());

        $status = $service->getAvailabilityStatus();
        $this->assertFalse($status['available']);
        $this->assertEquals('settings_not_found', $status['type']);
        $this->assertStringContainsString('podešavanja nisu pronađena', $status['message']);
    }

    /** @test */
    public function it_returns_configuration_error_for_api_requests_when_not_configured()
    {
        $service = SefService::forUser($this->user->id);

        $result = $service->getUnitMeasures();

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('configuration_error', $result['type']);
        $this->assertStringContainsString('nije konfigurisana ili omogućena', $result['error']);
    }

    /** @test */
    public function it_can_create_service_for_authenticated_user()
    {
        $this->actingAs($this->user);

        // Create settings for the authenticated user
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key',
        ]);

        $service = SefService::forAuthenticatedUser();

        $this->assertTrue($service->isConfigured());
        $this->assertEquals($this->user->id, $service->getSefSettings()->user_id);
    }

    #[Test]
    public function it_omits_large_successful_json_responses_without_losing_invoice_ids(): void
    {
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key',
        ]);

        $responseBody = json_encode([
            'salesInvoiceId' => 123456,
            'invoiceId' => 654321,
            'requestId' => 'sef-request-123',
            'padding' => str_repeat('x', 1048577),
        ]);

        Http::fake([
            '*' => Http::response($responseBody, 200, [
                'Content-Type' => 'application/json',
                'Content-Length' => (string) strlen($responseBody),
            ]),
        ]);

        $response = SefService::forUser($this->user->id)->sendInvoice('<Invoice />', 'Yes');

        $this->assertSame(123456, $response['salesInvoiceId']);
        $this->assertSame(654321, $response['invoiceId']);
        $this->assertSame('sef-request-123', $response['requestId']);
        $this->assertTrue($response['response_omitted']);
        $this->assertSame(strlen($responseBody), $response['response_size']);
        $this->assertArrayNotHasKey('padding', $response);
    }

    #[Test]
    public function it_ignores_non_company_values_in_company_responses(): void
    {
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key',
        ]);

        Http::fake([
            '*' => Http::response([
                true,
                [
                    'Id' => 123,
                    'Name' => 'Test Company',
                    'PIB' => '123456789',
                    'IsSefEnabled' => true,
                ],
            ]),
        ]);

        $response = SefService::forUser($this->user->id)->getAllCompanies();

        $this->assertCount(1, $response['companies']);
        $this->assertSame('Test Company', $response['companies'][0]->name);
        $this->assertSame('123456789', $response['companies'][0]->pib);
    }

    #[Test]
    public function it_sends_jbkjs_when_checking_a_budget_company_registration(): void
    {
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key',
        ]);

        Http::fake([
            '*' => Http::response([
                'eFakturaRegisteredCompany' => true,
            ]),
        ]);

        $response = SefService::forUser($this->user->id)
            ->checkIfCompanyRegisteredOnEfaktura('108213413', '17862146', '10520');

        $this->assertTrue($response['eFakturaRegisteredCompany']);

        Http::assertSent(function ($request): bool {
            return $request['vatNumber'] === '108213413'
                && $request['registrationNumber'] === '17862146'
                && $request['jbkjs'] === '10520';
        });
    }

    #[Test]
    public function it_requires_jbkjs_when_sef_reports_that_pib_belongs_to_a_budget_user(): void
    {
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key',
        ]);

        Http::fake([
            '*' => Http::response('CompanyWithVATRegistrationCodeIsBudgetUser', 400, [
                'Content-Type' => 'text/plain',
            ]),
        ]);

        $response = SefService::forUser($this->user->id)->searchCompanyByPib('108213413');

        $this->assertFalse($response['is_registered']);
        $this->assertTrue($response['requires_jbkjs']);
        $this->assertSame([], $response['companies']);
    }

    /** @test */
    public function it_handles_multiple_users_independently()
    {
        $user2 = User::factory()->create();

        // Create settings for first user only
        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'user1-api-key',
        ]);

        $service1 = SefService::forUser($this->user->id);
        $service2 = SefService::forUser($user2->id);

        $this->assertTrue($service1->isConfigured());
        $this->assertFalse($service2->isConfigured());

        $config1 = $service1->getConfigurationStatus();
        $config2 = $service2->getConfigurationStatus();

        $this->assertEquals($this->user->id, $config1['user_id']);
        $this->assertNull($config2['user_id']);
    }
}
