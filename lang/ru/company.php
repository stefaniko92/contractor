<?php

return [
    'title' => 'Информация о компании',
    'page_title' => 'Информация о компании',
    'navigation_label' => 'Информация о компании',

    'fields' => [
        'company_name' => 'Название компании',
        'company_full_name' => 'Полное название компании',
        'company_tax_id' => 'ИНН',
        'company_registry_number' => 'Регистрационный номер',
        'company_activity_code' => 'Код деятельности',
        'company_activity_desc' => 'Описание деятельности',
        'company_registration_date' => 'Дата регистрации',
        'company_city' => 'Город',
        'company_postal_code' => 'Почтовый индекс',
        'company_status' => 'Статус',
        'company_municipality' => 'Муниципалитет',
        'company_address' => 'Адрес',
        'company_address_number' => 'Номер дома',
        'company_phone' => 'Телефон',
        'company_email' => 'Email',
        'show_email_on_invoice' => 'Показать email на счете',
        'company_logo_path' => 'Логотип компании',
    ],

    'actions' => [
        'save' => 'Сохранить',
    ],

    'notifications' => [
        'saved' => 'Информация о компании успешно сохранена!',
    ],
];
