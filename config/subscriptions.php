<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Define your subscription plans here. Each plan should have a Stripe
    | Price ID that you'll create in your Stripe dashboard.
    |
    */

    'plans' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Up to 3 invoices per month',
            'price' => 0,
            'currency' => 'RSD',
            'interval' => 'month',
            'features' => [
                '3 fakture mesečno',
                'Osnovna fakturisanja',
                'Evidencija klijenata',
                'PDF izvoz',
            ],
            'limits' => [
                'monthly_invoices' => 3,
            ],
        ],

        'basic_monthly' => [
            'name' => 'Basic - Mesečno',
            'description' => 'Unlimited invoices, monthly billing',
            'price' => 600,
            'currency' => 'RSD',
            'interval' => 'month',
            'stripe_price_id' => env('STRIPE_BASIC_MONTHLY_PRICE_ID', ''), // Add this in .env after creating in Stripe
            'features' => [
                'Neograničen broj faktura',
                'Sva osnovna fakturisanja',
                'Evidencija klijenata',
                'PDF izvoz',
                'Email podrška',
                '7 dana besplatno probnog perioda',
            ],
            'limits' => [
                'monthly_invoices' => PHP_INT_MAX,
            ],
            'trial_days' => 7,
        ],

        'basic_yearly' => [
            'name' => 'Basic - Godišnje',
            'description' => 'Unlimited invoices, yearly billing (2 months free)',
            'price' => 6000, // 10 months * 600 RSD
            'currency' => 'RSD',
            'interval' => 'year',
            'stripe_price_id' => env('STRIPE_BASIC_YEARLY_PRICE_ID', ''), // Add this in .env after creating in Stripe
            'features' => [
                'Neograničen broj faktura',
                'Sva osnovna fakturisanja',
                'Evidencija klijenata',
                'PDF izvoz',
                'Email podrška',
                'Ušteda od 2 meseca',
                '7 dana besplatno probnog perioda',
            ],
            'limits' => [
                'monthly_invoices' => PHP_INT_MAX,
            ],
            'trial_days' => 7,
            'savings' => 1200, // 2 months free
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Subscription Plan
    |--------------------------------------------------------------------------
    |
    | The default plan name that will be used when creating subscriptions.
    |
    */

    'default_plan' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Free Plan Limits
    |--------------------------------------------------------------------------
    |
    | Limits for users on the free plan (not subscribed and not grandfathered)
    |
    */

    'free_limits' => [
        'monthly_invoices' => 3,
    ],
];
