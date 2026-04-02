<?php

return [

    'title' => 'Postavite šifru',

    'heading' => 'Postavite šifru',

    'form' => [

        'email' => [
            'label' => 'Email adresa',
        ],

        'password' => [
            'label' => 'Šifra',
            'validation_attribute' => 'šifra',
        ],

        'password_confirmation' => [
            'label' => 'Potvrdite šifru',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Postavi šifru',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Previše pokušaja',
            'body' => 'Molimo pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
