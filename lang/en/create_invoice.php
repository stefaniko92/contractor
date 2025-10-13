<?php

return [
    'title' => 'New Invoice',
    'navigation_label' => 'New Invoice',

    'sections' => [
        'invoice_type' => [
            'title' => 'Invoice Type',
            'description' => 'Select invoice type based on client location',
        ],
        'client_selection' => [
            'title' => 'Client Selection',
            'description' => 'Select existing client or create new one',
        ],
        'participants_info' => [
            'title' => 'Participants Information',
            'description' => 'Information about your company and client',
        ],
        'basic_info' => [
            'title' => 'Basic Invoice Information',
        ],
        'invoice_items' => [
            'title' => 'Invoice Items',
            'description' => 'Add items with prices',
        ],
    ],

    'fields' => [
        'invoice_type' => [
            'label' => 'Invoice Type',
            'options' => [
                'domestic' => 'Domestic Invoice',
                'foreign' => 'Foreign Invoice',
            ],
        ],
        'client_id' => [
            'label' => 'Clients',
            'placeholder' => 'Search clients...',
        ],
        'company_info' => [
            'label' => 'Issuer (Your Company)',
        ],
        'client_info' => [
            'label' => 'Buyer (Client)',
            'select_client' => 'Select a client to view information',
            'not_found' => 'Client not found',
        ],
        'invoice_number' => [
            'label' => 'Invoice Number',
            'placeholder' => 'Leave empty for auto-generation',
            'helper' => 'If you don\'t enter a number, it will be auto-generated',
        ],
        'issue_date' => [
            'label' => 'Issue Date',
        ],
        'due_date' => [
            'label' => 'Due Date',
        ],
        'trading_place' => [
            'label' => 'Place of Supply',
        ],
        'currency' => [
            'label' => 'Currency',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'invoice_items' => [
            'label' => 'Items',
            'type' => 'Type',
            'description' => 'Name',
            'unit' => 'Unit',
            'quantity' => 'Quantity',
            'unit_price' => 'Price',
            'discount' => 'Discount',
            'discount_type' => 'Disc. Type',
            'total' => 'Total',
        ],
        'invoice_total' => 'INVOICE TOTAL',
    ],

    'item_types' => [
        'service' => 'Service',
        'product' => 'Product',
    ],

    'units' => [
        'kom' => 'piece',
        'sat' => 'hour',
        'm' => 'm',
        'm2' => 'm2',
        'm3' => 'm3',
        'kg' => 'kg',
        'l' => 'l',
        'pak' => 'package',
        'reč' => 'word',
        'dan' => 'day',
    ],

    'currencies' => [
        'RSD' => 'RSD - Serbian Dinar',
        'EUR' => 'EUR - Euro',
        'USD' => 'USD - US Dollar',
    ],

    'discount_types' => [
        'percent' => '%',
        'fixed' => ':currency',
    ],

    'client_form' => [
        'company_name' => 'Company Name',
        'tax_id' => 'TAX ID',
        'address' => 'Address',
        'city' => 'City',
        'country' => 'Country',
        'vat_number' => 'VAT/TAX Number',
        'registration_number' => 'ID/REG Number',
        'email' => 'Email',
        'phone' => 'Phone',
    ],

    'actions' => [
        'save' => 'Save',
        'issue' => 'Issue Invoice',
        'issue_and_send' => 'Issue and Send',
    ],

    'notifications' => [
        'error_no_client' => [
            'title' => 'Error',
            'body' => 'You must select a client',
        ],
        'saved' => [
            'title' => 'Invoice Saved',
            'body' => 'Invoice number :number has been saved as draft. You can issue it later.',
        ],
        'issued' => [
            'title' => 'Invoice Issued',
            'body' => 'Invoice number :number has been successfully issued and is ready to send.',
        ],
        'sent' => [
            'title' => 'Invoice Issued and Sent',
            'body' => 'Invoice number :number has been successfully issued and marked as sent.',
        ],
        'copied_from_invoice' => [
            'title' => 'Data Copied',
            'body' => 'Data has been successfully copied from :type :number. You can modify it before creating.',
        ],
        'copied_from_profaktura' => [
            'title' => 'Data Copied',
            'body' => 'Data has been successfully copied from proforma invoice :number. You can modify it before creating invoice.',
        ],
    ],

    'document_types' => [
        'faktura' => 'invoice',
        'profaktura' => 'proforma invoice',
        'avansna_faktura' => 'advance invoice',
        'default' => 'document',
    ],
];
