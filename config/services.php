<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'dompetx' => [
        'api_key' => env('DOMPETX_API_KEY'),
        'secret_key' => env('DOMPETX_SECRET_KEY'),
        'merchant_id' => env('DOMPETX_MERCHANT_ID'),
        'api_url' => env('DOMPETX_API_URL', 'https://dompetx.com/api/v1'),
    ],

    'pakasir' => [
        'api_key' => env('PAKASIR_API_KEY'),
        'secret_key' => env('PAKASIR_SECRET_KEY'),
        'merchant_id' => env('PAKASIR_MERCHANT_ID'),
        'api_url' => env('PAKASIR_API_URL', 'https://pakasir.com/api/v1'),
    ],

    'turnstile' => [
        'enabled' => env('TURNSTILE_ENABLED', false),
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

    'fivesim' => [
        'api_key' => env('FIVESIM_API_KEY'),
        'api_url' => 'https://5sim.net/v1',
    ],

    'herosms' => [
        'api_key' => env('HEROSMS_API_KEY'),
        'api_url' => env('HEROSMS_API_URL', 'https://herosms.com/api/v1'),
    ],

    'ditznesia' => [
        'api_key' => env('DITZNESIA_API_KEY'),
        'api_url' => env('DITZNESIA_API_URL', 'https://api.ditznesia.id/v1'),
    ],

];
