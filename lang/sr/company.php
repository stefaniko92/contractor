<?php

return [
    'title' => 'Podaci o kompaniji',
    'page_title' => 'Podaci o kompaniji',
    'navigation_label' => 'Podaci o kompaniji',

    'fields' => [
        'company_name' => 'Naziv kompanije',
        'company_full_name' => 'Pun naziv kompanije',
        'company_tax_id' => 'PIB',
        'company_registry_number' => 'Matični broj',
        'company_activity_code' => 'Šifra delatnosti',
        'company_activity_desc' => 'Opis delatnosti',
        'company_registration_date' => 'Datum registracije',
        'company_city' => 'Grad',
        'company_postal_code' => 'Poštanski broj',
        'company_status' => 'Status',
        'company_municipality' => 'Opština',
        'company_address' => 'Adresa',
        'company_address_number' => 'Broj adrese',
        'company_phone' => 'Telefon',
        'company_email' => 'Email',
        'show_email_on_invoice' => 'Prikaži email na fakturi',
        'company_logo_path' => 'Logo kompanije',
    ],

    'actions' => [
        'save' => 'Sačuvaj',
    ],

    'notifications' => [
        'saved' => 'Podaci o kompaniji su sačuvani!',
    ],
];
