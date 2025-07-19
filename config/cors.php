<?php

return [

    'paths' => ['api/*', 'fallback_login'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://staging-admin.kaleidoscope.com.au',
    ],

    'allowed_origins_patterns' => ['/^https:\/\/.*\.kaleidoscope\.com\.au$/'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

