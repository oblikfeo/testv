<?php

return [

    // Узлы подписки задаются в .env как готовые URI (см. .env.example).
    // Список того, что реально отдаётся клиентам, и подписи к узлам —
    // в SharedVpnAccess::NODE_LABELS: ключ здесь должен совпадать с ключом там,
    // иначе URI просто не попадёт в /sub.
    //
    // Первый узел подписки: VLESS Reality, «🇩🇪 Домашний интернет».
    'shared_home_uri' => env('SHARED_HOME_URI', ''),

    // Второй узел: VLESS xhttp через Yandex Cloud CDN, «🇷🇺 Сотовая сеть».
    'shared_cellular_uri' => env('SHARED_CELLULAR_URI', ''),

    'trial' => [
        'duration_hours' => (int) env('TRIAL_DURATION_HOURS', 3),
        'soft_quota_gb' => (int) env('TRIAL_SOFT_QUOTA_GB', 0),
    ],

];
