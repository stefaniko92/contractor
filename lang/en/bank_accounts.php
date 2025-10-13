<?php

return [
    'title' => 'Bank Accounts',
    'page_title' => 'Bank Accounts',
    'navigation_label' => 'Bank Accounts',

    'section_title' => 'Bank Account Management',
    'section_description' => 'Add and manage your company\'s domestic and foreign bank accounts',

    'new_account' => 'New Account',

    'fields' => [
        'account_type' => 'Account Type',
        'bank' => 'Bank',
        'account_number' => 'Account Number',
        'iban' => 'IBAN',
        'bank_name' => 'Bank Name',
        'swift' => 'SWIFT Code',
        'currency' => 'Currency',
        'is_primary' => 'Primary Account',
    ],

    'types' => [
        'domestic' => 'Domestic',
        'foreign' => 'Foreign',
    ],

    'help' => [
        'domestic_currency' => 'Domestic accounts use RSD currency',
        'swift_auto' => 'SWIFT code is automatically populated based on selected bank',
        'is_primary' => 'Mark this as the company\'s main account',
    ],

    'actions' => [
        'add_account' => 'Add Account',
        'save' => 'Save',
    ],

    'notifications' => [
        'saved' => 'Bank accounts saved successfully!',
        'company_required' => 'Please fill company info first.',
    ],
];
