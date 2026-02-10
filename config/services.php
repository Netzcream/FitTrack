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

    'mercadopago' => [
        'base_url' => env('MERCADOPAGO_BASE_URL', 'https://api.mercadopago.com'),
        'runtime_env' => env('MERCADOPAGO_RUNTIME_ENV', 'local'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'expo' => [
        'enabled' => env('EXPO_PUSH_ENABLED', false),
        'send_url' => env('EXPO_PUSH_SEND_URL', 'https://exp.host/--/api/v2/push/send'),
        'receipts_url' => env('EXPO_PUSH_RECEIPTS_URL', 'https://exp.host/--/api/v2/push/getReceipts'),
        'access_token' => env('EXPO_PUSH_ACCESS_TOKEN'),
    ],

];
