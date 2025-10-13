<?php

return [
    'title' => 'Dokumenta',
    'page_title' => 'Dokumenta',
    'navigation_label' => 'Dokumenta',

    'fields' => [
        'name' => 'Naziv',
        'description' => 'Opis',
        'document_type' => 'Tip dokumenta',
        'file' => 'Fajl',
        'file_name' => 'Naziv fajla',
        'file_size' => 'Veličina',
        'uploaded_at' => 'Otpremljeno',
    ],

    'types' => [
        'invoice' => 'Faktura',
        'contract' => 'Ugovor',
        'certificate' => 'Sertifikat',
        'license' => 'Licenca',
        'tax_document' => 'Poreski dokument',
        'other' => 'Ostalo',
    ],

    'actions' => [
        'upload' => 'Otpremi dokument',
        'download' => 'Preuzmi',
        'delete' => 'Obriši',
    ],

    'notifications' => [
        'uploaded' => 'Dokument je uspešno otpremljen!',
        'deleted' => 'Dokument je obrisan.',
    ],
];
