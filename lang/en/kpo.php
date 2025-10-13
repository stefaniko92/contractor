<?php

return [
    'title' => 'KPO Book',
    'page_title' => 'KPO Income Book',
    'navigation_label' => 'KPO Book',

    'section_heading' => 'Income and Expense Books by Year',
    'section_description' => 'Download the KPO book for the selected year. The book contains all issued invoices and income.',

    'fields' => [
        'year' => 'Year',
        'invoice_count' => 'Invoice Count',
        'total_amount' => 'Total Amount',
        'actions' => 'Actions',
    ],

    'actions' => [
        'download' => 'Download KPO',
    ],

    'no_invoices' => 'You have no invoices to display. Create your first invoice to generate a KPO book.',

    // PDF content
    'pdf' => [
        'title' => 'INCOME AND EXPENSE BOOK',
        'subtitle' => 'for flat-rate taxation',
        'year' => 'Year',
        'page' => 'Page',
        'of' => 'of',

        'table_headers' => [
            'no' => 'No.',
            'date' => 'Date',
            'document' => 'Document type and number',
            'client' => 'Legal/Individual name',
            'description' => 'Short description',
            'amount' => 'Amount (RSD)',
            'note' => 'Note',
        ],

        'footer' => [
            'total' => 'TOTAL',
            'income' => 'Total income',
            'entrepreneur' => 'Entrepreneur',
            'signature' => 'Signature',
        ],

        'company_info' => [
            'name' => 'Entrepreneur',
            'pib' => 'TAX ID',
            'mb' => 'REG No.',
            'address' => 'Address',
            'activity' => 'Activity',
        ],
    ],
];
