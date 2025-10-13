<?php

return [
    'title' => 'Documents',
    'page_title' => 'Documents',
    'navigation_label' => 'Documents',

    'fields' => [
        'name' => 'Name',
        'description' => 'Description',
        'document_type' => 'Document Type',
        'file' => 'File',
        'file_name' => 'File Name',
        'file_size' => 'Size',
        'uploaded_at' => 'Uploaded At',
    ],

    'types' => [
        'invoice' => 'Invoice',
        'contract' => 'Contract',
        'certificate' => 'Certificate',
        'license' => 'License',
        'tax_document' => 'Tax Document',
        'other' => 'Other',
    ],

    'actions' => [
        'upload' => 'Upload Document',
        'download' => 'Download',
        'delete' => 'Delete',
    ],

    'notifications' => [
        'uploaded' => 'Document uploaded successfully!',
        'deleted' => 'Document deleted.',
    ],
];
