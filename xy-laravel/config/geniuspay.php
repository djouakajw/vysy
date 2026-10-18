<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GeniusPay API Configuration
    |--------------------------------------------------------------------------
    |
    | These values MUST be set in your .env file on the Hostinger server.
    | Never commit real keys to version control.
    |
    */

    'base_url' => env('GENIUSPAY_BASE_URL', 'https://api.geniuspay.com'),
    'api_key' => env('GENIUSPAY_API_KEY', ''),
    'secret_key' => env('GENIUSPAY_SECRET_KEY', ''),
    'webhook_secret' => env('GENIUSPAY_WEBHOOK_SECRET', ''),
    'timeout' => env('GENIUSPAY_TIMEOUT', 30),

];
