<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    // Cookieless metric, self-hosted (the umami service in docker-compose.yml). Loaded only in production.
    'umami' => [
        'script_url' => env('UMAMI_SCRIPT_URL'),
        'website_id' => env('UMAMI_WEBSITE_ID'),
    ],

    // Sign-in for members. Scopes are fixed in code: openid, email, profile.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    // Sandbox or live by PAYPAL_MODE; without a client id, local dev simulates payments.
    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    // Sandbox or production by MELHOR_ENVIO_ENV; without a token, quotes are a marked simulation.
    'melhor_envio' => [
        'env' => env('MELHOR_ENVIO_ENV', 'sandbox'),
        'token' => env('MELHOR_ENVIO_TOKEN'),
        'from_postal_code' => env('MELHOR_ENVIO_FROM_POSTAL_CODE'),
        'user_agent' => env('MELHOR_ENVIO_USER_AGENT', 'OVNIPORTO (contato@ovniporto.tars.art.br)'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

];
