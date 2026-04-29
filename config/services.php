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

    'telegram_bot' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'username' => env('TELEGRAM_BOT_USERNAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IndexNow (Yandex / Bing / Naver / Seznam)
    |--------------------------------------------------------------------------
    |
    | IndexNow позволяет мгновенно сообщать поисковикам об обновлении страниц,
    | чтобы индексация занимала минуты вместо недель.
    | Подробнее: https://yandex.ru/support/webmaster/indexnow/
    |
    | INDEXNOW_KEY — секретный ключ (8–128 символов, [a-f0-9-]).
    | Если не задан, ключ генерируется детерминированно из APP_KEY.
    | Файл подтверждения отдаётся по пути /<key>.txt роутом приложения.
    |
    */
    'indexnow' => [
        'key' => env('INDEXNOW_KEY'),
        'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
    ],

];
