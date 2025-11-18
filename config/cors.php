<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin requests such as
    | allowed paths, methods, origins, and headers. This ensures both your
    | admin and retailer portals can communicate with the API securely.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://retailer.localhost:4200',
        'http://admin.localhost:4201',
        'https://staging-admin.kaleidoscope.com.au',
        'https://staging-retailer.kaleidoscope.com.au',
        'https://admin.kaleidoscope.com.au',
        'https://retailer.kaleidoscope.com.au',
        'https://dropshipping.kaleidoscope.com.au',
        'https://toydrop.com.au',
    ],

    /*'allowed_origins' => ['*'],*/

    /*'allowed_origins' => [
        'http://retailer.localhost:4200',
        'http://admin.localhost:4201',
    ],*/



    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
