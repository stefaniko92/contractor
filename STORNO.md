# Invoice Storno (Reversal) System

## Overview

This system implements proper invoice storno (reversal) functionality according to Serbian law and accounting practices for paušalac freelancers.

> **Note:**  
> According to Serbian law and accounting practice for paušalac freelancers, when an invoice is canceled or needs to be reversed (storno), a separate reversal invoice must be created with negative values for the same items and amounts. Both the original and the reversal invoice must be recorded in the Book of Income (Knjiga prihoda), ensuring transparency and compliance. The original invoice should never be deleted, only reversed.

## How It Works

### 1. Storno Process

When a user clicks the "Storniraj" (Storno) action on an invoice:

1. **Validation**: The system checks that:
   - The invoice is not already a storno invoice (`is_storno = false`)
   - The invoice doesn't already have a storno invoice created for it

2. **Create Reversal Invoice**: A new invoice is created with:
   - **Negative amounts**: All amounts (total and per item) are negative
   - **Reference to original**: Links back to the original invoice
   - **Same items**: All invoice items are duplicated with negative values
   - **Clear description**: Shows it's a storno of the original invoice

3. **Update Original**: The original invoice status is changed to "storned"

4. **Book of Income Impact**: Both invoices are recorded, resulting in net zero effect

### 2. Database Structure

New fields added to `invoices` table:

```php
$table->boolean('is_storno')->default(false);
$table->unsignedBigInteger('original_invoice_id')->nullable();
$table->string('original_invoice_number')->nullable();
$table->date('original_invoice_date')->nullable();
```

### 3. Visual Indicators

- **Storno invoices** are marked with "(STORNO)" in the invoice number column
- **Red color** is used to highlight storno invoices in the table
- **Storno action** only appears for eligible invoices

## Legal Compliance

This implementation ensures compliance with Serbian regulations:

1. **No Deletion**: Original invoices are never deleted
2. **Full Audit Trail**: Both original and storno invoices are preserved
3. **Negative Amounts**: Storno invoices use proper negative values
4. **Book of Income**: Both entries appear in income records
5. **Reference Tracking**: Clear link between original and storno invoices

## Example

Original Invoice:
- Invoice #123/2025: +10,000 RSD
- Items: Web Development +10,000 RSD

Storno Invoice:
- Invoice #124/2025: -10,000 RSD (STORNO)
- Items: Storno: Web Development -10,000 RSD
- Description: "Storno fakture 123/2025 od 07.08.2025"
- References original invoice #123/2025

**Net Effect in Book of Income**: 0 RSD (+10,000 - 10,000)

## Usage

1. Navigate to Invoices, Profakture, or Avansne Fakture
2. Find the invoice to storno
3. Click the actions dropdown (⋮)
4. Click "Storniraj" (X mark icon)
5. Confirm the action in the modal
6. System creates storno invoice automatically
7. Both invoices remain in the system for compliance