<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/public/*', 'api/public/contact'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => env('APP_ENV') === 'local'
        ? ['*'] // Allow all origins in development
        : array_filter([
            'https://pausalci.com',
            'https://www.pausalci.com',
        ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
