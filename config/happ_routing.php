<?php

$appUrl = rtrim((string) env('APP_URL', ''), '/');

return [
    'enabled' => env('HAPP_ROUTING_ENABLED', true),

    // Новое имя — Happ перезапишет старый профиль с тяжёлыми геофайлами (30 МБ).
    'name' => env('HAPP_ROUTING_NAME', 'AVA RU Direct'),

    // Lite geofiles (~500 KB). Полные Loyalsoldier (30 MB) не успевают скачаться за 3 мин на LTE.
    'use_builtin_geo' => env('HAPP_GEO_USE_BUILTIN', true),
    'use_chunk_files' => env('HAPP_GEO_USE_CHUNK_FILES', true),
    'last_updated' => env('HAPP_ROUTING_LAST_UPDATED', '20260608'),

    'geoip_url' => env('HAPP_GEOIP_URL', $appUrl.'/geo/geoip.dat'),
    'geosite_url' => env('HAPP_GEOSITE_URL', $appUrl.'/geo/geosite.dat'),

    'direct_sites' => [
        'geosite:category-ru',
        'domain:tbank.ru',
        'domain:tinkoff.ru',
        'domain:tcsbank.ru',
        'domain:sberbank.ru',
        'domain:sber.ru',
        'domain:vtb.ru',
        'domain:alfabank.ru',
        'domain:gosuslugi.ru',
        'domain:ozon.ru',
        'domain:wildberries.ru',
        'domain:wb.ru',
        'domain:avito.ru',
        'domain:lan',
        'domain:local',
    ],

    'direct_ip' => [
        'geoip:ru',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '100.64.0.0/10',
        '224.0.0.0/4',
        '240.0.0.0/4',
        '255.255.255.255/32',
    ],

    'proxy_sites' => [],
    'proxy_ip' => [],
];
