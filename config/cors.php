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

    'paths' => ['api/*', 'login', 'logout', 'auth/me', 'neto-products', 'products/*', 'shipping/cost','dropship-orders/*'],

    'allowed_methods' => ['*'],

    /*'allowed_origins' => [
        'http://retailer.localhost:4200',
        'http://admin.localhost:4201',
        'http://localhost:4200',
        'https://staging-admin.kaleidoscope.com.au',
        'https://staging-retailer.kaleidoscope.com.au',
        'https://admin.kaleidoscope.com.au',
        'https://retailer.kaleidoscope.com.au',
    ],*/

    'allowed_origins' => ['*'],

    /*'allowed_origins' => [
        'http://retailer.localhost:4200',
        'http://admin.localhost:4201',
    ],*/



    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // necessary for session cookies

];
