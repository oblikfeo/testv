<?php

return [

    // Узлы подписки задаются в .env как готовые URI (см. .env.example).
    // Первый узел подписки (VLESS Reality, Wi-Fi).
    'shared_hy2_uri' => env('SHARED_HY2_URI', ''),

    // Необязательный узел (в /sub сейчас не отдаётся).
    'shared_vless_uri' => env('SHARED_VLESS_URI', ''),

    // CDN xhttp (белые списки операторов) — узел «обход блокировок» в подписке.
    'shared_cdn_uri' => env('SHARED_CDN_URI', ''),

    'trial' => [
        'duration_hours' => (int) env('TRIAL_DURATION_HOURS', 3),
        'soft_quota_gb' => (int) env('TRIAL_SOFT_QUOTA_GB', 0),
    ],

];
