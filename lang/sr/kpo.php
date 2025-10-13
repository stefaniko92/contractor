<?php

return [
    'title' => 'KPO knjiga',
    'page_title' => 'KPO knjiga prihoda',
    'navigation_label' => 'KPO knjiga',

    'section_heading' => 'Knjiga prihoda i rashoda po godinama',
    'section_description' => 'Preuzmite KPO knjigu za odabranu godinu. Knjiga sadrži sve izdate fakture i prihode.',

    'fields' => [
        'year' => 'Godina',
        'invoice_count' => 'Broj faktura',
        'total_amount' => 'Ukupan iznos',
        'actions' => 'Akcije',
    ],

    'actions' => [
        'download' => 'Preuzmi KPO',
    ],

    'no_invoices' => 'Nemate faktura za prikazivanje. Kreirajte prvu fakturu da biste mogli generisati KPO knjigu.',

    // PDF content
    'pdf' => [
        'title' => 'KNJIGA PRIHODA I RASHODA',
        'subtitle' => 'za paušalno oporezivanje',
        'year' => 'Godina',
        'page' => 'Strana',
        'of' => 'od',

        'table_headers' => [
            'no' => 'R.br.',
            'date' => 'Datum',
            'document' => 'Vrsta i broj dokumenta',
            'client' => 'Naziv pravnog/fizičkog lica',
            'description' => 'Kratak opis',
            'amount' => 'Iznos (RSD)',
            'note' => 'Napomena',
        ],

        'footer' => [
            'total' => 'UKUPNO',
            'income' => 'Ukupni prihodi',
            'entrepreneur' => 'Preduzetnik',
            'signature' => 'Potpis',
        ],

        'company_info' => [
            'name' => 'Preduzetnik',
            'pib' => 'PIB',
            'mb' => 'MB',
            'address' => 'Adresa',
            'activity' => 'Delatnost',
        ],
    ],
];
