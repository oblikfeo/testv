<?php

$appUrl = rtrim((string) env('APP_URL', ''), '/');

return [
    'enabled' => env('HAPP_ROUTING_ENABLED', true),

    'name' => env('HAPP_ROUTING_NAME', 'AVA RU Direct'),

    // Lite geofiles на сайте (~500 KB). Builtin + geosite:category-ru ломали маршрутизацию.
    'use_builtin_geo' => env('HAPP_GEO_USE_BUILTIN', false),
    'use_chunk_files' => env('HAPP_GEO_USE_CHUNK_FILES', true),
    'route_order' => 'block-proxy-direct',
    'last_updated' => env('HAPP_ROUTING_LAST_UPDATED', '20260608b'),

    'geoip_url' => env('HAPP_GEOIP_URL', $appUrl.'/geo/geoip.dat'),
    'geosite_url' => env('HAPP_GEOSITE_URL', $appUrl.'/geo/geosite.dat'),

    // Зарубежные/заблокированные — явно через VPN (иначе Happ пускает напрямую с SIM → не работает).
    'proxy_sites' => [
        'geosite:youtube',
        'geosite:telegram',
        'geosite:instagram',
        'geosite:facebook',
        'geosite:twitter',
        'geosite:google',
        'geosite:discord',
        'geosite:whatsapp',
        'geosite:tiktok',
        'geosite:netflix',
        'geosite:spotify',
        'geosite:openai',
    ],

    'proxy_ip' => [
        'geoip:telegram',
    ],

    'direct_sites' => [
        'geosite:ru',
        'geosite:yandex',
        'geosite:mailru',
        'geosite:vk',
        'domain:tbank.ru',
        'domain:tinkoff.ru',
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
];
