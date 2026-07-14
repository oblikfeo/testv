<?php

$appUrl = rtrim((string) env('APP_URL', ''), '/');

return [
    // off | full_tunnel | split
    'mode' => env('HAPP_ROUTING_MODE', 'split'),

    'enabled' => env('HAPP_ROUTING_ENABLED', true),

    'name' => env('HAPP_ROUTING_NAME', 'AVA Split RU'),

    // Lite geofiles на /geo (~500 KB). Теги geosite:category-ru / geoip:private — под DigneZzZ lite.
    'use_builtin_geo' => env('HAPP_GEO_USE_BUILTIN', false),
    'use_chunk_files' => env('HAPP_GEO_USE_CHUNK_FILES', false),
    'route_order' => 'block-proxy-direct',
    // Unix timestamp — Happ перекачивает geo при увеличении значения.
    'last_updated' => env('HAPP_ROUTING_LAST_UPDATED', '1784060400'),

    'announcement' => env('HAPP_SUBSCRIPTION_ANNOUNCE', ''),
    'subscription_pin' => env('HAPP_SUBSCRIPTION_PIN', true),

    'geoip_url' => env('HAPP_GEOIP_URL', $appUrl.'/geo/geoip.dat'),
    'geosite_url' => env('HAPP_GEOSITE_URL', $appUrl.'/geo/geosite.dat'),

    // DNS split: зарубежное через VPN — Google; российское напрямую — Yandex.
    'remote_dns_type' => 'DoH',
    'remote_dns_domain' => env('HAPP_REMOTE_DNS_DOMAIN', 'https://8.8.8.8/dns-query'),
    'remote_dns_ip' => env('HAPP_REMOTE_DNS_IP', '8.8.8.8'),
    'domestic_dns_type' => 'DoH',
    'domestic_dns_domain' => env('HAPP_DOMESTIC_DNS_DOMAIN', 'https://77.88.8.8/dns-query'),
    'domestic_dns_ip' => env('HAPP_DOMESTIC_DNS_IP', '77.88.8.8'),

    'proxy_sites' => [
        'geosite:github',
        'geosite:twitch-ads',
        'geosite:youtube',
        'geosite:telegram',
        'geosite:discord',
        'geosite:whatsapp',
        'geosite:google-deepmind',
        'geosite:crypto',
        'geosite:instagram',
        'geosite:facebook',
        'geosite:twitter',
        'geosite:google',
        'geosite:tiktok',
        'geosite:netflix',
        'geosite:spotify',
        'geosite:openai',
    ],

    'proxy_ip' => [
        'geoip:telegram',
    ],

    'direct_sites' => [
        'geosite:private',
        'geosite:category-ru',
        'geosite:whitelist',
        'geosite:microsoft',
        'geosite:apple',
        'geosite:google-play',
        'geosite:epicgames',
        'geosite:riot',
        'geosite:escapefromtarkov',
        'geosite:steam',
        'geosite:origin',
        'geosite:twitch',
        'geosite:pinterest',
        'geosite:faceit',
        'geosite:ip-check',
        'geosite:vpndetect',
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
        'geoip:private',
        'geoip:whitelist',
    ],

    'block_sites' => [
        'geosite:win-spy',
        'geosite:torrent',
        'geosite:category-ads',
    ],

    'block_ip' => [],
];
