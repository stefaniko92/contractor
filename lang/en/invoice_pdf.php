<?php

return [
    'invoice' => 'Invoice',
    'proforma' => 'Proforma Invoice',
    'advance_invoice' => 'Advance Invoice',
    'invoice_number' => 'Number',
    'invoice_date' => 'Invoice Date',
    'transaction_date' => 'Transaction Date',
    'place' => 'Place',
    'from' => 'From',
    'to' => 'To',
    'pib' => 'TAX ID',
    'reg_number' => 'Reg. No',
    'bank_account' => 'Bank Account',
    'email' => 'Email',

    'table' => [
        'service_description' => 'Service Description',
        'unit' => 'Unit',
        'quantity' => 'Qty',
        'price' => 'Price',
        'discount' => 'Discount %',
        'total' => 'Total',
    ],

    'totals' => [
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'total_amount' => 'Total Amount',
    ],

    'payment' => [
        'title' => 'Payment Information',
        'due_in_days' => 'Payment due within :days days',
        'reference' => 'Payment reference',
        'payment_reference_note' => 'When paying the invoice, please include reference number :number',
        'invoice_id' => 'Identification number',
        'valid_without_signature' => 'Invoice is valid without seal and signature',
        'issue_place' => 'Place of issue',
    ],

    'tax_notice' => [
        'title' => 'Tax Exemption Notice',
        'message' => 'The taxpayer is not in the VAT system.<br>VAT is not calculated on the invoice in accordance with Article 33 of the Value Added Tax Law.',
    ],

    'footer' => 'Invoice created on :platform',
];
