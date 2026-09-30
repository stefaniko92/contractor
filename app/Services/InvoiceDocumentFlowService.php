<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceAdvanceApplication;
use App\Models\InvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceDocumentFlowService
{
    public function createAdvanceFromProforma(Invoice $profaktura, float $amount): Invoice
    {
        $this->ensureProfaktura($profaktura);

        $remainingAmount = $this->remainingProformaAmount($profaktura);

        if ($amount <= 0 || $amount > $remainingAmount) {
            throw ValidationException::withMessages([
                'amount' => "Iznos avansa mora biti veći od nule i najviše {$remainingAmount} {$profaktura->currency}.",
            ]);
        }

        return DB::transaction(function () use ($amount, $profaktura): Invoice {
            $advance = Invoice::create([
                ...$this->documentAttributes($profaktura),
                'invoice_document_type' => 'avansna_faktura',
                'source_profaktura_id' => $profaktura->id,
                'description' => "Avans po profakturi {$profaktura->invoice_number}",
                'amount' => 0,
                'status' => 'in_preparation',
            ]);

            InvoiceItem::create([
                'invoice_id' => $advance->id,
                'title' => "Avans po profakturi {$profaktura->invoice_number}",
                'description' => "Avansno plaćanje po profakturi {$profaktura->invoice_number}",
                'type' => 'service',
                'unit' => 'kom',
                'quantity' => 1,
                'unit_price' => $amount,
                'amount' => $amount,
                'currency' => $profaktura->currency,
                'discount_value' => 0,
                'discount_type' => 'percent',
            ]);

            $advance->updateAmount();

            return $advance->refresh();
        });
    }

    /**
     * @param  Collection<int, Invoice>  $advances
     */
    public function createFinalInvoiceFromProforma(Invoice $profaktura, Collection $advances): Invoice
    {
        $this->ensureProfaktura($profaktura);

        $advances->each(function (Invoice $advance) use ($profaktura): void {
            if ($advance->source_profaktura_id !== $profaktura->id
                || $advance->invoice_document_type !== 'avansna_faktura'
                || $advance->status !== 'charged') {
                throw ValidationException::withMessages([
                    'advance_invoice_ids' => 'Mogu se koristiti samo naplaćene avansne fakture vezane za ovu profakturu.',
                ]);
            }
        });

        $appliedAmounts = $advances->mapWithKeys(function (Invoice $advance): array {
            return [$advance->id => $this->availableAdvanceAmount($advance)];
        })->filter(fn (float $amount): bool => $amount > 0);

        $appliedTotal = (float) $appliedAmounts->sum();
        if ($appliedTotal <= 0 || $appliedTotal > (float) $profaktura->amount) {
            throw ValidationException::withMessages([
                'advance_invoice_ids' => 'Izabrani avansi nemaju raspoloživ iznos za umanjenje ove fakture.',
            ]);
        }

        return DB::transaction(function () use ($appliedAmounts, $profaktura): Invoice {
            $invoice = Invoice::create([
                ...$this->documentAttributes($profaktura),
                'invoice_document_type' => 'faktura',
                'source_profaktura_id' => $profaktura->id,
                'description' => "Konačna faktura po profakturi {$profaktura->invoice_number}",
                'amount' => 0,
                'status' => 'in_preparation',
            ]);

            foreach ($profaktura->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'type' => $item->type,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                    'currency' => $item->currency,
                    'discount_value' => $item->discount_value,
                    'discount_type' => $item->discount_type,
                ]);
            }

            foreach ($appliedAmounts as $advanceId => $amount) {
                $advance = Invoice::findOrFail($advanceId);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'title' => "Umanjenje za avans {$advance->invoice_number}",
                    'description' => "Umanjenje po avansnoj fakturi {$advance->invoice_number}",
                    'type' => 'service',
                    'unit' => 'kom',
                    'quantity' => 1,
                    'unit_price' => -$amount,
                    'amount' => -$amount,
                    'currency' => $invoice->currency,
                    'discount_value' => 0,
                    'discount_type' => 'percent',
                ]);

                InvoiceAdvanceApplication::create([
                    'invoice_id' => $invoice->id,
                    'advance_invoice_id' => $advance->id,
                    'applied_amount' => $amount,
                ]);
            }

            $invoice->updateAmount();

            return $invoice->refresh();
        });
    }

    public function createCreditNote(Invoice $originalInvoice, float $amount, string $reason): Invoice
    {
        if ($originalInvoice->invoice_document_type !== 'faktura' || $originalInvoice->is_storno) {
            throw ValidationException::withMessages([
                'invoice' => 'Knjižno odobrenje se može izdati samo za redovnu fakturu.',
            ]);
        }

        $availableAmount = $this->availableCreditAmount($originalInvoice);
        if ($amount <= 0 || $amount > $availableAmount) {
            throw ValidationException::withMessages([
                'amount' => "Iznos odobrenja mora biti veći od nule i najviše {$availableAmount} {$originalInvoice->currency}.",
            ]);
        }

        if (blank($reason)) {
            throw ValidationException::withMessages([
                'reason' => 'Razlog knjižnog odobrenja je obavezan.',
            ]);
        }

        return DB::transaction(function () use ($amount, $originalInvoice, $reason): Invoice {
            $creditNote = Invoice::create([
                ...$this->documentAttributes($originalInvoice),
                'invoice_document_type' => 'knjizno_odobrenje',
                'original_invoice_id' => $originalInvoice->id,
                'original_invoice_number' => $originalInvoice->invoice_number,
                'original_invoice_date' => $originalInvoice->issue_date,
                'credit_note_reason' => $reason,
                'description' => "Knjižno odobrenje za fakturu {$originalInvoice->invoice_number}: {$reason}",
                'amount' => 0,
                'status' => 'issued',
            ]);

            InvoiceItem::create([
                'invoice_id' => $creditNote->id,
                'title' => "Knjižno odobrenje za {$originalInvoice->invoice_number}",
                'description' => $reason,
                'type' => 'service',
                'unit' => 'kom',
                'quantity' => 1,
                'unit_price' => -$amount,
                'amount' => -$amount,
                'currency' => $originalInvoice->currency,
                'discount_value' => 0,
                'discount_type' => 'percent',
            ]);

            $creditNote->updateAmount();

            return $creditNote->refresh();
        });
    }

    public function remainingProformaAmount(Invoice $profaktura): float
    {
        $issuedAdvances = $profaktura->advanceInvoices()
            ->where('status', '!=', 'storned')
            ->sum('amount');

        return max(0, round((float) $profaktura->amount - (float) $issuedAdvances, 2));
    }

    public function availableAdvanceAmount(Invoice $advance): float
    {
        $appliedAmount = $advance->advanceApplicationsAsAdvance()->sum('applied_amount');

        return max(0, round((float) $advance->amount - (float) $appliedAmount, 2));
    }

    public function availableCreditAmount(Invoice $invoice): float
    {
        $creditedAmount = abs((float) $invoice->creditNotes()->sum('amount'));

        return max(0, round((float) $invoice->amount - $creditedAmount, 2));
    }

    private function ensureProfaktura(Invoice $profaktura): void
    {
        if ($profaktura->invoice_document_type !== 'profaktura' || $profaktura->is_storno) {
            throw ValidationException::withMessages([
                'profaktura' => 'Ovaj dokument nije aktivna profaktura.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function documentAttributes(Invoice $source): array
    {
        return [
            'user_id' => $source->user_id,
            'client_id' => $source->client_id,
            'bank_account_id' => $source->bank_account_id,
            'invoice_type' => $source->invoice_type,
            'issue_date' => now(),
            'delivery_date' => now(),
            'due_date' => $source->due_date,
            'trading_place' => $source->trading_place,
            'currency' => $source->currency,
            'order_reference' => $source->order_reference,
            'contract_reference' => $source->contract_reference,
            'lot_reference' => $source->lot_reference,
        ];
    }
}
