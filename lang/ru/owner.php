<?php

return [
    'title' => 'Информация о владельце',
    'page_title' => 'Информация о владельце',
    'navigation_label' => 'Информация о владельце',

    'fields' => [
        'first_name' => 'Имя',
        'last_name' => 'Фамилия',
        'parent_name' => 'Отчество',
        'nationality' => 'Национальность',
        'personal_id_number' => 'Личный идентификационный номер',
        'education_level' => 'Уровень образования',
        'gender' => 'Пол',
        'city' => 'Город',
        'municipality' => 'Муниципалитет',
        'address' => 'Адрес',
        'address_number' => 'Номер дома',
        'email' => 'Email',
    ],

    'gender_options' => [
        'male' => 'Мужской',
        'female' => 'Женский',
        'other' => 'Другое',
    ],

    'actions' => [
        'save' => 'Сохранить',
    ],

    'notifications' => [
        'saved' => 'Информация о владельце успешно сохранена!',
        'company_required' => 'Пожалуйста, сначала заполните информацию о компании.',
    ],
];
