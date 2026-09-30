<?php

namespace Tests\Feature;

use App\Filament\Pages\CreateInvoicePage;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SefEfakturaSetting;
use App\Models\User;
use App\Models\UserCompany;
use App\Services\InvoiceDocumentFlowService;
use App\Services\Sef\InvoiceValidator;
use App\Services\Sef\RecipientResolver;
use App\Services\Sef\VatProfileResolver;
use App\Services\SefService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_create_invoice_page_does_not_display_fallback_company_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateInvoicePage::class)
            ->assertSee('Unesite podatke o kompaniji pre kreiranja fakture.')
            ->assertDontSee('STEFAN RAKIĆ PR RAČUNARSKO PROGRAMIRANJE SR SOFTWARE NIŠ')
            ->assertDontSee('109270190');
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

    public function test_create_invoice_page_issues_invoice_when_using_the_default_create_action(): void
    {
        $client = $this->createClient();
        $bankAccount = $this->createBankAccount();

        Livewire::test(CreateInvoicePage::class)
            ->fillForm([
                'client_id' => $client->id,
                'bank_account_id' => $bankAccount->id,
                'invoice_number' => 'ISSUED-001/2026',
                'description' => 'Issued through the default action',
                'invoice_items' => [[
                    'type' => 'service',
                    'description' => 'Consulting',
                    'unit' => 'sat',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_value' => 0,
                    'discount_type' => 'percent',
                    'total' => 100,
                ]],
            ])
            ->call('create');

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'ISSUED-001/2026',
            'status' => 'issued',
        ]);
    }

    public function test_create_invoice_page_saves_drafts_only_when_the_draft_action_is_selected(): void
    {
        $client = $this->createClient();
        $bankAccount = $this->createBankAccount();

        Livewire::test(CreateInvoicePage::class)
            ->fillForm([
                'client_id' => $client->id,
                'bank_account_id' => $bankAccount->id,
                'invoice_number' => 'DRAFT-001/2026',
                'description' => 'Saved as a deliberate draft',
                'invoice_items' => [[
                    'type' => 'service',
                    'description' => 'Consulting',
                    'unit' => 'sat',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount_value' => 0,
                    'discount_type' => 'percent',
                    'total' => 100,
                ]],
            ])
            ->call('saveAsDraft');

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'DRAFT-001/2026',
            'status' => 'in_preparation',
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

    public function test_pausal_income_excludes_drafts_and_storno_invoices(): void
    {
        $client = $this->createClient();

        $issuedInvoice = $this->createInvoice($client, [
            'invoice_number' => '1/2026',
            'amount' => 100,
            'status' => 'issued',
        ]);
        $issuedInvoice->updateQuietly(['amount' => 100]);
        $this->createInvoice($client, [
            'invoice_number' => '2/2026',
            'amount' => 200,
            'status' => 'in_preparation',
        ]);
        $this->createInvoice($client, [
            'invoice_number' => '3/2026',
            'amount' => -100,
            'status' => 'storned',
            'is_storno' => true,
        ]);

        $income = Invoice::query()
            ->where('user_id', $this->user->id)
            ->countsTowardsPausalIncome()
            ->sum('amount');

        $this->assertSame(100.0, (float) $income);
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
            'delivery_date' => now(),
            'order_reference' => 'NAR-14/2025',
            'contract_reference' => 'UG-2025-14',
            'lot_reference' => 'LOT-3',
        ]);
        $this->createInvoiceItem($invoice);

        $xml = $invoice->generateUblXml();

        $this->assertStringContainsString('<cac:OrderReference>', $xml);
        $this->assertStringContainsString('<cbc:ID>NAR-14/2025</cbc:ID>', $xml);
        $this->assertStringContainsString('<cac:ContractDocumentReference>', $xml);
        $this->assertStringContainsString('<cbc:ID>UG-2025-14</cbc:ID>', $xml);
        $this->assertStringContainsString('<cac:AdditionalDocumentReference>', $xml);
        $this->assertStringContainsString('<cbc:ID>LOT-3</cbc:ID>', $xml);
    }

    public function test_budget_user_invoice_requires_a_real_procurement_reference_before_sending_to_sef(): void
    {
        $this->userCompany->update([
            'company_tax_id' => '109270190',
            'company_address' => 'Bulevar oslobođenja 1',
        ]);

        SefEfakturaSetting::factory()->create([
            'user_id' => $this->user->id,
            'is_enabled' => true,
            'api_key' => 'test-api-key',
            'default_vat_exemption' => 'PDV-RS-33',
            'default_vat_category' => 'SS',
        ]);

        Http::fake([
            '*' => Http::response([[
                'key' => 'PDV-RS-33',
                'text' => 'Mali poreski obveznik.',
            ]]),
        ]);

        $client = $this->createClient([
            'jbkjs' => '80596',
            'efaktura_verified' => true,
            'efaktura_status' => 'active',
        ]);
        $invoice = $this->createInvoice($client, [
            'delivery_date' => now(),
        ]);
        $this->createInvoiceItem($invoice);

        $sefService = SefService::forUser($this->user->id);
        $validation = (new InvoiceValidator(
            new VatProfileResolver($sefService),
            new RecipientResolver($sefService),
        ))->validate($invoice);

        $this->assertTrue($validation->hasErrors());
        $this->assertContains(
            'Za budžetskog korisnika unesite broj narudžbenice, ugovora ili partije.',
            $validation->errors,
        );
    }

    public function test_it_creates_a_final_invoice_with_a_partial_advance_from_a_profaktura(): void
    {
        $client = $this->createClient();
        $profaktura = $this->createInvoice($client, [
            'invoice_number' => 'P1/2026',
            'invoice_document_type' => 'profaktura',
            'amount' => 1000,
        ]);
        $this->createInvoiceItem($profaktura, [
            'amount' => 1000,
            'unit_price' => 1000,
        ]);
        $profaktura->updateAmount();

        $flow = app(InvoiceDocumentFlowService::class);
        $advance = $flow->createAdvanceFromProforma($profaktura, 300);
        $this->assertSame(700.0, $flow->remainingProformaAmount($profaktura));
        $advance->update(['status' => 'charged']);

        $invoice = $flow->createFinalInvoiceFromProforma($profaktura, collect([$advance]));

        $this->assertSame('avansna_faktura', $advance->invoice_document_type);
        $this->assertSame($profaktura->id, $advance->source_profaktura_id);
        $this->assertSame('faktura', $invoice->invoice_document_type);
        $this->assertSame($profaktura->id, $invoice->source_profaktura_id);
        $this->assertSame(700.0, (float) $invoice->amount);
        $this->assertDatabaseHas('invoice_advance_applications', [
            'invoice_id' => $invoice->id,
            'advance_invoice_id' => $advance->id,
            'applied_amount' => 300,
        ]);
        $this->assertSame(0.0, $flow->availableAdvanceAmount($advance));
    }

    public function test_it_creates_a_partial_credit_note_linked_to_the_original_invoice(): void
    {
        $client = $this->createClient();
        $invoice = $this->createInvoice($client, [
            'invoice_number' => '20/2026',
            'amount' => 1000,
            'status' => 'issued',
        ]);
        $this->createInvoiceItem($invoice, [
            'amount' => 1000,
            'unit_price' => 1000,
        ]);
        $invoice->updateAmount();

        $flow = app(InvoiceDocumentFlowService::class);
        $creditNote = $flow->createCreditNote($invoice, 250, 'Umanjenje naknade po dogovoru');

        $this->assertSame('knjizno_odobrenje', $creditNote->invoice_document_type);
        $this->assertSame($invoice->id, $creditNote->original_invoice_id);
        $this->assertSame($invoice->invoice_number, $creditNote->original_invoice_number);
        $this->assertSame(-250.0, (float) $creditNote->amount);
        $this->assertSame(750.0, $flow->availableCreditAmount($invoice));

        $xml = $creditNote->generateUblXml();
        $this->assertStringContainsString('<cbc:InvoiceTypeCode>381</cbc:InvoiceTypeCode>', $xml);
        $this->assertStringContainsString('<cac:BillingReference>', $xml);
        $this->assertStringContainsString('<cbc:ID>20/2026</cbc:ID>', $xml);
    }

    public function test_foreign_invoice_preview_shows_swift_and_full_sender_details(): void
    {
        $this->user->update([
            'email' => 'stefan@example.com',
            'swift_code' => 'BICCODE123',
            'iban' => 'RS35160600000082564121',
        ]);

        $this->userCompany->update([
            'company_name' => 'SR SOFTWARE NIS',
            'company_full_name' => 'STEFAN RAKIC PR RACUNARSKO PROGRAMIRANJE SR SOFTWARE NIS',
            'company_tax_id' => '109270190',
            'company_registry_number' => '64056891',
            'company_address' => 'Branka Radicevica',
            'company_address_number' => '26a/92',
            'company_city' => 'Nis',
            'company_postal_code' => '18000',
            'company_email' => 'stefan@example.com',
            'show_email_on_invoice' => true,
        ]);

        $client = $this->createClient([
            'company_name' => 'Janus Trade d.o.o',
            'address' => 'Koroska Cesta 53c',
            'city' => 'Kranj',
            'country' => 'Slovenija',
            'is_domestic' => false,
            'currency' => 'EUR',
            'tax_id' => '576986799',
        ]);

        $bankAccount = $this->createBankAccount([
            'account_type' => 'foreign',
            'currency' => 'EUR',
            'iban' => 'RS35160600000082564121',
            'bank_name' => 'Banca Intesa',
            'swift' => 'DBDBRSBG',
            'account_number' => null,
        ]);

        $invoice = $this->createInvoice($client, [
            'bank_account_id' => $bankAccount->id,
            'invoice_type' => 'foreign',
            'currency' => 'EUR',
        ]);

        $response = $this->get(route('invoices.preview', $invoice));

        $response->assertOk();
        $response->assertSee('STEFAN RAKIC PR RACUNARSKO PROGRAMIRANJE SR SOFTWARE NIS');
        $response->assertSee('Banca Intesa');
        $response->assertSee('RS35160600000082564121');
        $response->assertSee('DBDBRSBG');
        $response->assertSee('Slovenija');
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
