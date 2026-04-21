<?php

namespace Tests\Feature;

use App\Filament\Pages\CreateInvoicePage;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\UserCompany;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected UserCompany $userCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_grandfathered' => true,
        ]);

        $this->userCompany = UserCompany::create([
            'user_id' => $this->user->id,
            'company_name' => 'Test Company',
        ]);

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_create_invoice_page_preselects_primary_bank_account(): void
    {
        $bankAccount = $this->createBankAccount();

        Livewire::test(CreateInvoicePage::class)
            ->assertFormSet([
                'currency' => 'RSD',
                'bank_account_id' => $bankAccount->id,
            ]);
    }

    public function test_edit_invoice_page_preselects_primary_bank_account_when_missing(): void
    {
        $bankAccount = $this->createBankAccount();
        $client = $this->createClient();
        $invoice = $this->createInvoice($client);

        Livewire::test(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])->assertFormSet([
            'bank_account_id' => $bankAccount->id,
        ]);
    }

    public function test_edit_invoice_action_can_mark_invoice_as_sent(): void
    {
        $client = $this->createClient();
        $invoice = $this->createInvoice($client);

        Livewire::test(EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])->callAction('mark_as_sent');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'sent',
        ]);
    }

    public function test_bulk_action_marks_selected_invoices_as_sent(): void
    {
        $client = $this->createClient();
        $firstInvoice = $this->createInvoice($client, [
            'invoice_number' => '1/2026',
            'status' => 'in_preparation',
        ]);
        $secondInvoice = $this->createInvoice($client, [
            'invoice_number' => '2/2026',
            'status' => 'issued',
        ]);
        $stornoInvoice = $this->createInvoice($client, [
            'invoice_number' => '3/2026',
            'status' => 'storned',
            'is_storno' => true,
        ]);

        Livewire::test(ListInvoices::class)
            ->callTableBulkAction('mark_as_sent', [$firstInvoice, $secondInvoice, $stornoInvoice]);

        $this->assertDatabaseHas('invoices', [
            'id' => $firstInvoice->id,
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $secondInvoice->id,
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $stornoInvoice->id,
            'status' => 'storned',
        ]);
    }

    public function test_budget_user_invoice_ubl_includes_order_reference(): void
    {
        $client = $this->createClient([
            'jbkjs' => '80596',
            'efaktura_verified' => true,
            'efaktura_status' => 'active',
        ]);
        $invoice = $this->createInvoice($client, [
            'invoice_number' => '14/2025',
        ]);
        $this->createInvoiceItem($invoice);

        $xml = $invoice->generateUblXml();

        $this->assertStringContainsString('<cac:OrderReference>', $xml);
        $this->assertStringContainsString('<cbc:ID>14/2025</cbc:ID>', $xml);
    }

    protected function createClient(array $attributes = []): Client
    {
        return Client::create(array_merge([
            'user_id' => $this->user->id,
            'company_name' => 'Client Company',
            'tax_id' => '123456789',
            'address' => 'Client Address',
            'is_domestic' => true,
            'currency' => 'RSD',
        ], $attributes));
    }

    protected function createBankAccount(array $attributes = []): BankAccount
    {
        return BankAccount::create(array_merge([
            'user_company_id' => $this->userCompany->id,
            'account_number' => '160-0000000000000-00',
            'bank_name' => 'Test Bank',
            'account_type' => 'domestic',
            'currency' => 'RSD',
            'is_primary' => true,
        ], $attributes));
    }

    protected function createInvoice(Client $client, array $attributes = []): Invoice
    {
        return Invoice::create(array_merge([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'invoice_number' => '10/2026',
            'amount' => 100,
            'description' => 'Test invoice',
            'currency' => 'RSD',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'trading_place' => 'Beograd',
            'status' => 'in_preparation',
            'invoice_type' => 'domestic',
            'invoice_document_type' => 'faktura',
            'bank_account_id' => null,
            'is_storno' => false,
        ], $attributes));
    }

    protected function createInvoiceItem(Invoice $invoice, array $attributes = []): InvoiceItem
    {
        return InvoiceItem::create(array_merge([
            'invoice_id' => $invoice->id,
            'title' => 'Hosting',
            'description' => 'Hosting service',
            'type' => 'service',
            'unit' => 'kom',
            'quantity' => 1,
            'unit_price' => 100,
            'amount' => 100,
        ], $attributes));
    }
}
