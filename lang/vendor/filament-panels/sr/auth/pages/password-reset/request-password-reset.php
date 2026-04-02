<?php

return [

    'title' => 'Resetovanje šifre',

    'heading' => 'Zaboravili ste šifru?',

    'actions' => [

        'login' => [
            'label' => 'nazad na prijavu',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Email adresa',
        ],

        'actions' => [

            'request' => [
                'label' => 'Pošalji email',
            ],

        ],

    ],

    'notifications' => [

        'sent' => [
            'body' => 'Ako vaš nalog ne postoji, nećete dobiti email.',
        ],

        'throttled' => [
            'title' => 'Previše zahteva',
            'body' => 'Molimo pokušajte ponovo za :seconds sekundi.',
        ],

    ],

];
