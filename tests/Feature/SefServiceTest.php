<?php

namespace Tests\Feature;

use App\Models\SefEfakturaSetting;
use App\Models\User;
use App\Services\SefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
