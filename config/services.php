<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        // Jeda antar email pada pengiriman massal (ms). Sesuaikan dengan Maximum Send Rate akun SES.
        'throttle_delay_ms' => (int) env('SES_THROTTLE_DELAY_MS', 100),
        // Jumlah percobaan maksimal per email untuk error throttling.
        'max_retries' => (int) env('SES_MAX_RETRIES', 3),
        // Basis backoff eksponensial (ms) antar retry.
        'backoff_base_ms' => (int) env('SES_BACKOFF_BASE_MS', 1000),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
