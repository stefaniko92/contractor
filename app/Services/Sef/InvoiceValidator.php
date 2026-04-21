<?php

namespace App\Services\Sef;

use App\Models\Invoice;

class InvoiceValidator
{
    private VatProfileResolver $vatProfileResolver;
    private RecipientResolver $recipientResolver;

    public function __construct(
        VatProfileResolver $vatProfileResolver,
        RecipientResolver $recipientResolver
    ) {
        $this->vatProfileResolver = $vatProfileResolver;
        $this->recipientResolver = $recipientResolver;
    }

    /**
     * Validate invoice before sending to SEF
     */
    public function validate(Invoice $invoice): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Load relationships
        $invoice->load(['client', 'user.userCompany', 'items', 'bankAccount']);

        // 1. Validate client has required fields
        if (!$invoice->client) {
            $errors[] = 'Invoice must have a client';
            return new ValidationResult(false, $errors, $warnings);
        }

        // 2. Validate client PIB
        if (!$invoice->client->tax_id) {
            $errors[] = 'Client must have a PIB (tax_id)';
        }

        // 3. Validate client eFaktura status
        if ($invoice->client->efaktura_status !== 'active' && !$invoice->client->allow_efaktura_bypass) {
            $errors[] = 'Client is not registered in eFaktura system';
        }

        // 4. Validate budget user has JBKJS
        if (!empty($invoice->client->jbkjs)) {
            // This is a budget user
            if (strlen($invoice->client->jbkjs) < 3) {
                $errors[] = 'Budget user JBKJS code is invalid';
            }
        }

        // 5. Validate VAT profile
        $context = [
            'is_pausalni' => true, // Assume paušalci
            'vat_rate' => 0,
        ];

        $vatProfile = $this->vatProfileResolver->resolveForInvoice($context);
        $vatErrors = $this->vatProfileResolver->validate($vatProfile);
        if ($vatErrors) {
            $errors = array_merge($errors, $vatErrors);
        }

        // 6. Validate invoice has items
        if ($invoice->items->isEmpty()) {
            $errors[] = 'Invoice must have at least one item';
        }

        // 7. Validate invoice amounts
        if ($invoice->amount <= 0) {
            $errors[] = 'Invoice amount must be greater than 0';
        }

        // 8. Validate bank account
        if (!$invoice->bankAccount) {
            $warnings[] = 'Invoice has no bank account - payment instructions will be incomplete';
        }

        // 9. Validate user company details
        if (!$invoice->user->userCompany) {
            $errors[] = 'User must have company details configured';
        } else {
            $company = $invoice->user->userCompany;
            if (!$company->company_tax_id) {
                $errors[] = 'User company must have PIB';
            }
            if (!$company->company_name) {
                $errors[] = 'User company must have name';
            }
            if (!$company->company_address) {
                $warnings[] = 'User company address is missing';
            }
        }

        // 10. Validate dates are in Serbian timezone
        $serbianDate = now()->timezone('Europe/Belgrade')->format('Y-m-d');
        if ($invoice->issue_date && $invoice->issue_date->format('Y-m-d') !== $serbianDate) {
            $warnings[] = "Invoice will be sent with today's date ({$serbianDate}) as required by SEF";
        }

        $isValid = empty($errors);

        return new ValidationResult($isValid, $errors, $warnings);
    }
}

/**
 * Validation result
 */
class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly array $errors,
        public readonly array $warnings
    ) {}

    public function hasErrors(): bool
    {
        return !$this->isValid;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function getMessage(): string
    {
        if ($this->hasErrors()) {
            return 'Validation failed: ' . implode(', ', $this->errors);
        }

        if ($this->hasWarnings()) {
            return 'Validation passed with warnings: ' . implode(', ', $this->warnings);
        }

        return 'Validation passed';
    }

    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
}
