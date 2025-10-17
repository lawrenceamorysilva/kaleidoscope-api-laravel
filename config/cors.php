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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'admin/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://staging-admin.kaleidoscope.com.au',
        'https://staging-retailer.kaleidoscope.com.au',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // necessary for session cookies

];
