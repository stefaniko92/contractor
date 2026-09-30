<?php

namespace Tests\Feature;

use App\Filament\Pages\Analytics;
use App\Filament\Widgets\TwelveMonthIncomeProgress;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\PausalIncomeAnalytics;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PausalIncomeAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_a_365_day_projection_and_expiring_income(): void
    {
        Carbon::setTestNow('2026-09-30 12:00:00');

        $user = User::factory()->create();
        $domesticClient = Client::create([
            'user_id' => $user->id,
            'company_name' => 'Domaći klijent',
            'tax_id' => '123456789',
            'address' => 'Beograd',
            'is_domestic' => true,
            'currency' => 'RSD',
        ]);
        $foreignClient = Client::create([
            'user_id' => $user->id,
            'company_name' => 'Foreign client',
            'tax_id' => 'US-123',
            'address' => 'New York',
            'is_domestic' => false,
            'currency' => 'USD',
        ]);

        $this->createInvoice($user, $domesticClient, '2025-11-01', 100);
        $this->createInvoice($user, $domesticClient, '2026-04-15', 200);
        $this->createInvoice($user, $domesticClient, '2026-10-15', 300);
        $this->createInvoice($user, $domesticClient, '2026-05-01', 400, 'in_preparation');
        $this->createInvoice($user, $foreignClient, '2026-05-01', 500);

        $analytics = app(PausalIncomeAnalytics::class)->forDate($user->id, Carbon::parse('2026-11-01'));

        $this->assertSame(500.0, $analytics['income']);
        $this->assertSame(7_999_500.0, $analytics['remaining']);
        $this->assertSame(100.0, $analytics['falling_off']);
        $this->assertSame(200.0, $analytics['change_from_today']);
        $this->assertSame('2025-11-02', $analytics['period_start']->toDateString());
        $this->assertSame('2026-11-01', $analytics['period_end']->toDateString());
    }

    public function test_dashboard_projection_widget_explains_the_365_day_calculation(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(TwelveMonthIncomeProgress::class)
            ->assertSee('Pokretni limit od 8M RSD')
            ->assertSee('Prikaži stanje na datum')
            ->assertSee('Projekcija koristi trenutno unete domaće fakture');
    }

    public function test_analytics_page_separates_currency_totals_and_lists_top_clients(): void
    {
        Carbon::setTestNow('2026-09-30 12:00:00');

        $user = User::factory()->create();
        $domesticClient = Client::create([
            'user_id' => $user->id,
            'company_name' => 'Najveći domaći klijent',
            'tax_id' => '123456789',
            'address' => 'Beograd',
            'is_domestic' => true,
            'currency' => 'RSD',
        ]);
        $foreignClient = Client::create([
            'user_id' => $user->id,
            'company_name' => 'Foreign client',
            'tax_id' => 'US-123',
            'address' => 'New York',
            'is_domestic' => false,
            'currency' => 'USD',
        ]);
        $this->createInvoice($user, $domesticClient, '2026-09-01', 250);
        $this->createInvoice($user, $foreignClient, '2026-09-15', 35, 'charged');

        $this->actingAs($user);

        Livewire::test(Analytics::class)
            ->assertSee('Analitika fakturisanja')
            ->assertSee('Promet po mesecima')
            ->assertSee('Status naplate')
            ->assertSee('Najveći domaći klijent')
            ->assertSee('250 RSD')
            ->assertSee('35 USD')
            ->set('period', '30_days')
            ->assertSee('Poslednjih 30 dana');
    }

    public function test_custom_period_includes_both_boundary_dates(): void
    {
        $bounds = Analytics::periodBounds('custom', '2026-02-01', '2026-02-28');

        $this->assertSame('2026-02-01 00:00:00', $bounds['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-28 23:59:59', $bounds['end']->format('Y-m-d H:i:s'));
    }

    public function test_invalid_custom_period_keeps_the_last_applied_range(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Analytics::class)
            ->set('period', 'custom')
            ->set('dateFrom', '2026-09-01')
            ->set('dateTo', '2026-08-01')
            ->call('applyDateRange')
            ->assertHasErrors(['dateTo' => 'after_or_equal'])
            ->assertSet('appliedDateFrom', now()->startOfYear()->toDateString())
            ->set('dateTo', '2026-09-30')
            ->call('applyDateRange')
            ->assertHasNoErrors()
            ->assertSet('appliedDateFrom', '2026-09-01')
            ->assertSet('appliedDateTo', '2026-09-30')
            ->assertSee('01.09.2026 - 30.09.2026');
    }

    protected function createInvoice(User $user, Client $client, string $issueDate, float $amount, string $status = 'issued'): void
    {
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => fake()->unique()->numerify('####').'/2026',
            'amount' => $amount,
            'description' => 'Test invoice',
            'currency' => $client->currency,
            'issue_date' => $issueDate,
            'due_date' => Carbon::parse($issueDate)->addDays(30),
            'trading_place' => 'Beograd',
            'status' => $status,
            'invoice_type' => $client->is_domestic ? 'domestic' : 'foreign',
            'invoice_document_type' => 'faktura',
        ]);

        $invoice->updateQuietly(['amount' => $amount]);
    }
}
