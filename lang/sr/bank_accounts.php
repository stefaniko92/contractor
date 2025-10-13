<?php

return [
    'title' => 'Bankovni računi',
    'page_title' => 'Bankovni računi',
    'navigation_label' => 'Bankovni računi',

    'section_title' => 'Upravljanje bankovnim računima',
    'section_description' => 'Dodajte i upravljajte domaćim i stranim bankovnim računima vaše kompanije',

    'new_account' => 'Novi račun',

    'fields' => [
        'account_type' => 'Tip računa',
        'bank' => 'Banka',
        'account_number' => 'Broj računa',
        'iban' => 'IBAN',
        'bank_name' => 'Naziv banke',
        'swift' => 'SWIFT kod',
        'currency' => 'Valuta',
        'is_primary' => 'Primarni račun',
    ],

    'types' => [
        'domestic' => 'Domaći',
        'foreign' => 'Strani',
    ],

    'help' => [
        'domestic_currency' => 'Domaći računi koriste RSD valutu',
        'swift_auto' => 'SWIFT kod se automatski popunjava na osnovu izabrane banke',
        'is_primary' => 'Označite ako je ovo glavni račun kompanije',
    ],

    'actions' => [
        'add_account' => 'Dodaj račun',
        'save' => 'Sačuvaj',
    ],

    'notifications' => [
        'saved' => 'Bankovni računi su sačuvani!',
        'company_required' => 'Molimo prvo popunite podatke o kompaniji.',
    ],
];
