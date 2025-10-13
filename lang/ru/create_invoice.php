<?php

return [
    'title' => 'Новый счет',
    'navigation_label' => 'Новый счет',

    'sections' => [
        'invoice_type' => [
            'title' => 'Тип счета',
            'description' => 'Выберите тип счета на основе местоположения клиента',
        ],
        'client_selection' => [
            'title' => 'Выбор клиента',
            'description' => 'Выберите существующего клиента или создайте нового',
        ],
        'participants_info' => [
            'title' => 'Информация об участниках',
            'description' => 'Данные о вашей компании и клиенте',
        ],
        'basic_info' => [
            'title' => 'Основная информация счета',
        ],
        'invoice_items' => [
            'title' => 'Позиции счета',
            'description' => 'Добавьте позиции с ценами',
        ],
    ],

    'fields' => [
        'invoice_type' => [
            'label' => 'Тип счета',
            'options' => [
                'domestic' => 'Внутренний счет',
                'foreign' => 'Международный счет',
            ],
        ],
        'client_id' => [
            'label' => 'Клиенты',
            'placeholder' => 'Поиск клиентов...',
        ],
        'company_info' => [
            'label' => 'Эмитент (Ваша компания)',
        ],
        'client_info' => [
            'label' => 'Покупатель (Клиент)',
            'select_client' => 'Выберите клиента для просмотра информации',
            'not_found' => 'Клиент не найден',
        ],
        'invoice_number' => [
            'label' => 'Номер счета',
            'placeholder' => 'Оставьте пустым для автоматической генерации',
            'helper' => 'Если вы не введете номер, он будет сгенерирован автоматически',
        ],
        'issue_date' => [
            'label' => 'Дата выставления',
        ],
        'due_date' => [
            'label' => 'Срок оплаты',
        ],
        'trading_place' => [
            'label' => 'Место поставки',
        ],
        'currency' => [
            'label' => 'Валюта',
        ],
        'description' => [
            'label' => 'Описание',
        ],
        'invoice_items' => [
            'label' => 'Позиции',
            'type' => 'Тип',
            'description' => 'Название',
            'unit' => 'Единица',
            'quantity' => 'Количество',
            'unit_price' => 'Цена',
            'discount' => 'Скидка',
            'discount_type' => 'Тип скидки',
            'total' => 'Итого',
        ],
        'invoice_total' => 'ИТОГО ПО СЧЕТУ',
    ],

    'item_types' => [
        'service' => 'Услуга',
        'product' => 'Товар',
    ],

    'units' => [
        'kom' => 'штука',
        'sat' => 'час',
        'm' => 'м',
        'm2' => 'м2',
        'm3' => 'м3',
        'kg' => 'кг',
        'l' => 'л',
        'pak' => 'упаковка',
        'reč' => 'слово',
        'dan' => 'день',
    ],

    'currencies' => [
        'RSD' => 'RSD - Сербский динар',
        'EUR' => 'EUR - Евро',
        'USD' => 'USD - Доллар США',
    ],

    'discount_types' => [
        'percent' => '%',
        'fixed' => ':currency',
    ],

    'client_form' => [
        'company_name' => 'Название компании',
        'tax_id' => 'НИД',
        'address' => 'Адрес',
        'city' => 'Город',
        'country' => 'Страна',
        'vat_number' => 'VAT/НДС номер',
        'registration_number' => 'ID/Рег. номер',
        'email' => 'Email',
        'phone' => 'Телефон',
    ],

    'actions' => [
        'save' => 'Сохранить',
        'issue' => 'Выставить счет',
        'issue_and_send' => 'Выставить и отправить',
    ],

    'notifications' => [
        'error_no_client' => [
            'title' => 'Ошибка',
            'body' => 'Необходимо выбрать клиента',
        ],
        'saved' => [
            'title' => 'Счет сохранен',
            'body' => 'Счет номер :number сохранен как черновик. Вы можете выставить его позже.',
        ],
        'issued' => [
            'title' => 'Счет выставлен',
            'body' => 'Счет номер :number успешно выставлен и готов к отправке.',
        ],
        'sent' => [
            'title' => 'Счет выставлен и отправлен',
            'body' => 'Счет номер :number успешно выставлен и отмечен как отправленный.',
        ],
        'copied_from_invoice' => [
            'title' => 'Данные скопированы',
            'body' => 'Данные успешно скопированы из :type :number. Вы можете изменить их перед созданием.',
        ],
        'copied_from_profaktura' => [
            'title' => 'Данные скопированы',
            'body' => 'Данные успешно скопированы из счета-проформы :number. Вы можете изменить их перед созданием счета.',
        ],
    ],

    'document_types' => [
        'faktura' => 'счета',
        'profaktura' => 'счета-проформы',
        'avansna_faktura' => 'авансового счета',
        'default' => 'документа',
    ],
];
