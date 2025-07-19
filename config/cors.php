<?php

return [

    'paths' => ['api/*', 'fallback_login'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://admin.localhost:4201',                         // ✅ Local dev
        'http://retailer.localhost:4200',                         // ✅ Local dev
        'https://staging-admin.kaleidoscope.com.au',           // ✅ Staging frontend
    ],

    'allowed_origins_patterns' => [], // ✅ Leave empty to prioritize allowed_origins

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];

