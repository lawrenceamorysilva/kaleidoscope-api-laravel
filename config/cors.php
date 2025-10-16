<?php

return [

    'paths' => [
        'api/*',
        'auth/*',
        'fallback_login',
        'sanctum/csrf-cookie', // ✅ important for stateful + Sanctum
    ],

    'allowed_methods' => ['*'], // ✅ allow all HTTP methods

    'allowed_origins' => [
        // local
        'http://api.localhost:8000',
        'http://admin.localhost:4201',
        'http://retailer.localhost:4200',

        // staging
        'https://staging-admin.kaleidoscope.com.au',
        'https://staging-retailer.kaleidoscope.com.au',
        'https://staging-api.kaleidoscope.com.au',

        // prod
        'https://admin.kaleidoscope.com.au',
        'https://retailer.kaleidoscope.com.au',
        'https://api.kaleidoscope.com.au',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
