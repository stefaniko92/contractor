<?php

return [
    'invoice' => 'Faktura',
    'proforma' => 'Profaktura',
    'advance_invoice' => 'Avansna Faktura',
    'invoice_number' => 'Broj',
    'invoice_date' => 'Datum izdavanja',
    'transaction_date' => 'Datum prometa',
    'place' => 'Mesto',
    'from' => 'Izdavalac',
    'to' => 'Kupac',
    'pib' => 'PIB',
    'reg_number' => 'Matični broj',
    'bank_account' => 'Tekući račun',
    'email' => 'Email',

    'table' => [
        'service_description' => 'Opis usluge',
        'unit' => 'Jedinica',
        'quantity' => 'Količina',
        'price' => 'Cena',
        'discount' => 'Popust %',
        'total' => 'Ukupno',
    ],

    'totals' => [
        'subtotal' => 'Međuzbir',
        'discount' => 'Popust',
        'total_amount' => 'Ukupan iznos',
    ],

    'payment' => [
        'title' => 'Informacije o plaćanju',
        'due_in_days' => 'Rok plaćanja :days dana',
        'reference' => 'Poziv na broj',
        'payment_reference_note' => 'Pri plaćanju fakture navedite poziv na broj :number',
        'invoice_id' => 'Identifikacioni broj',
        'valid_without_signature' => 'Faktura je važeća bez pečata i potpisa',
        'issue_place' => 'Mesto izdavanja',
    ],

    'tax_notice' => [
        'title' => 'Napomena o poreskom oslobođenju',
        'message' => 'Poreski obveznik nije u sistemu PDV-a.<br>PDV nije obračunat na fakturi u skladu sa članom 33. Zakona o porezu na dodatu vrednost.',
    ],

    'footer' => 'Faktura kreirana na :platform',
];
