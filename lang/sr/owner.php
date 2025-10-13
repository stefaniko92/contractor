<?php

return [
    'title' => 'Podaci o vlasniku',
    'page_title' => 'Podaci o vlasniku',
    'navigation_label' => 'Podaci o vlasniku',

    'fields' => [
        'first_name' => 'Ime',
        'last_name' => 'Prezime',
        'parent_name' => 'Ime roditelja',
        'nationality' => 'Nacionalnost',
        'personal_id_number' => 'JMBG',
        'education_level' => 'Stepen obrazovanja',
        'gender' => 'Pol',
        'city' => 'Grad',
        'municipality' => 'Opština',
        'address' => 'Adresa',
        'address_number' => 'Broj adrese',
        'email' => 'Email',
    ],

    'gender_options' => [
        'male' => 'Muški',
        'female' => 'Ženski',
        'other' => 'Ostalo',
    ],

    'actions' => [
        'save' => 'Sačuvaj',
    ],

    'notifications' => [
        'saved' => 'Podaci o vlasniku su sačuvani!',
        'company_required' => 'Molimo prvo popunite podatke o kompaniji.',
    ],
];
