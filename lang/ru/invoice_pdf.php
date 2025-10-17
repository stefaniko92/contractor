<?php

return [
    'invoice' => 'Счет',
    'proforma' => 'Счет-проформа',
    'advance_invoice' => 'Авансовый счет',
    'invoice_number' => 'Номер',
    'invoice_date' => 'Дата выставления',
    'transaction_date' => 'Дата операции',
    'place' => 'Место',
    'from' => 'От',
    'to' => 'Покупатель',
    'pib' => 'ИНН',
    'reg_number' => 'Рег. номер',
    'bank_account' => 'Банковский счет',
    'email' => 'Email',

    'table' => [
        'service_description' => 'Описание услуги',
        'unit' => 'Единица',
        'quantity' => 'Кол-во',
        'price' => 'Цена',
        'discount' => 'Скидка %',
        'total' => 'Итого',
    ],

    'totals' => [
        'subtotal' => 'Промежуточный итог',
        'discount' => 'Скидка',
        'total_amount' => 'Общая сумма',
    ],

    'payment' => [
        'title' => 'Информация об оплате',
        'due_in_days' => 'Срок оплаты :days дней',
        'reference' => 'Ссылка на платеж',
        'invoice_id' => 'ID счета',
        'valid_without_signature' => 'Счет действителен без печати и подписи',
        'issue_place' => 'Место выдачи',
    ],

    'tax_notice' => [
        'title' => 'Уведомление об освобождении от налогов',
        'message' => 'Налогоплательщик не находится в системе НДС.<br>НДС не рассчитывается в счете в соответствии со статьей 33 Закона о налоге на добавленную стоимость.',
    ],

    'footer' => 'Счет создан на :platform',
];
