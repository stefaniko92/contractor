<?php

return [
    'title' => 'Документы',
    'page_title' => 'Документы',
    'navigation_label' => 'Документы',

    'fields' => [
        'name' => 'Название',
        'description' => 'Описание',
        'document_type' => 'Тип документа',
        'file' => 'Файл',
        'file_name' => 'Имя файла',
        'file_size' => 'Размер',
        'uploaded_at' => 'Загружено',
    ],

    'types' => [
        'invoice' => 'Счет',
        'contract' => 'Контракт',
        'certificate' => 'Сертификат',
        'license' => 'Лицензия',
        'tax_document' => 'Налоговый документ',
        'other' => 'Другое',
    ],

    'actions' => [
        'upload' => 'Загрузить документ',
        'download' => 'Скачать',
        'delete' => 'Удалить',
    ],

    'notifications' => [
        'uploaded' => 'Документ успешно загружен!',
        'deleted' => 'Документ удален.',
    ],
];
