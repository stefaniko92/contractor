<?php

return [
    'title' => 'Nova Faktura',
    'navigation_label' => 'Nova Faktura',

    'sections' => [
        'invoice_type' => [
            'title' => 'Tip fakture',
            'description' => 'Izaberite tip fakture na osnovu lokacije klijenta',
        ],
        'client_selection' => [
            'title' => 'Izbor klijenta',
            'description' => 'Izaberite postojeći klijent ili kreirajte novi',
        ],
        'participants_info' => [
            'title' => 'Informacije o učesnicima',
            'description' => 'Podaci o vašoj kompaniji i klijentu',
        ],
        'basic_info' => [
            'title' => 'Osnovne informacije fakture',
        ],
        'invoice_items' => [
            'title' => 'Stavke fakture',
            'description' => 'Dodajte stavke sa cenama',
        ],
    ],

    'fields' => [
        'invoice_type' => [
            'label' => 'Tip fakture',
            'options' => [
                'domestic' => 'Domaća faktura',
                'foreign' => 'Inostrana faktura',
            ],
        ],
        'client_id' => [
            'label' => 'Klijenti',
            'placeholder' => 'Pretraži klijente...',
        ],
        'company_info' => [
            'label' => 'Izdavalac (Vaša kompanija)',
        ],
        'client_info' => [
            'label' => 'Kupac (Klijent)',
            'select_client' => 'Izaberite klijenta da vidite informacije',
            'not_found' => 'Klijent nije pronađen',
        ],
        'invoice_number' => [
            'label' => 'Broj fakture',
            'placeholder' => 'Ostavite prazno za automatsko generisanje',
            'helper' => 'Ako ne unesete broj, automatski će se generisati',
        ],
        'issue_date' => [
            'label' => 'Datum izdavanja',
        ],
        'due_date' => [
            'label' => 'Datum dospeća',
        ],
        'trading_place' => [
            'label' => 'Mesto prometa',
        ],
        'currency' => [
            'label' => 'Valuta',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'invoice_items' => [
            'label' => 'Stavke',
            'type' => 'Tip',
            'description' => 'Naziv',
            'unit' => 'Jedinica',
            'quantity' => 'Količina',
            'unit_price' => 'Cena',
            'discount' => 'Popust',
            'discount_type' => 'Tip pop.',
            'total' => 'Ukupno',
        ],
        'invoice_total' => 'UKUPNO ZA FAKTURU',
    ],

    'item_types' => [
        'service' => 'Usluga',
        'product' => 'Proizvod',
    ],

    'units' => [
        'kom' => 'komad',
        'sat' => 'sat',
        'm' => 'm',
        'm2' => 'm2',
        'm3' => 'm3',
        'kg' => 'kg',
        'l' => 'l',
        'pak' => 'pak',
        'reč' => 'reč',
        'dan' => 'dan',
    ],

    'currencies' => [
        'RSD' => 'RSD - Srpski dinar',
        'EUR' => 'EUR - Evro',
        'USD' => 'USD - Američki dolar',
    ],

    'discount_types' => [
        'percent' => '%',
        'fixed' => ':currency',
    ],

    'client_form' => [
        'company_name' => 'Naziv kompanije',
        'tax_id' => 'PIB',
        'address' => 'Adresa',
        'city' => 'Grad',
        'country' => 'Zemlja',
        'vat_number' => 'VAT/EIB broj',
        'registration_number' => 'ID/MB broj',
        'email' => 'Email',
        'phone' => 'Telefon',
    ],

    'actions' => [
        'save' => 'Sačuvaj',
        'issue' => 'Izdaj fakturu',
        'issue_and_send' => 'Izdaj i pošalji',
    ],

    'notifications' => [
        'error_no_client' => [
            'title' => 'Greška',
            'body' => 'Morate izabrati klijenta',
        ],
        'saved' => [
            'title' => 'Faktura je sačuvana',
            'body' => 'Faktura broj :number je sačuvana kao nacrt. Možete je kasnije izdati.',
        ],
        'issued' => [
            'title' => 'Faktura je izdata',
            'body' => 'Faktura broj :number je uspešno izdata i spremna za slanje.',
        ],
        'sent' => [
            'title' => 'Faktura je izdata i poslana',
            'body' => 'Faktura broj :number je uspešno izdata i označena kao poslana.',
        ],
        'copied_from_invoice' => [
            'title' => 'Podaci kopirani',
            'body' => 'Podaci su uspešno kopirani iz :type :number. Možete da ih modifikujete pre kreiranja.',
        ],
        'copied_from_profaktura' => [
            'title' => 'Podaci kopirani',
            'body' => 'Podaci su uspešno kopirani iz profakture :number. Možete da ih modifikujete pre kreiranja fakture.',
        ],
    ],

    'document_types' => [
        'faktura' => 'fakture',
        'profaktura' => 'profakture',
        'avansna_faktura' => 'avansne fakture',
        'default' => 'dokumenta',
    ],
];
