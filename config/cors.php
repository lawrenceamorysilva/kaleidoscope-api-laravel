<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'admin/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://staging-admin.kaleidoscope.com.au',
        'https://staging-retailer.kaleidoscope.com.au',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'supports_credentials' => true,
];

