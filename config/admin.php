<?php

return [
    'login' => env('ADMIN_LOGIN', 'admin'),
    'password' => env('ADMIN_PASSWORD', 'secret'),

    /** Префикс имени узла в Happ: «AVA 🇳🇱 Нидерланды» */
    'happ_brand' => env('HAPP_BRAND', 'AVA'),

    'test_panel' => [
        'url' => env('TEST_PANEL_URL', 'http://158.160.253.217'),
        'username' => env('TEST_PANEL_USERNAME', 'admin555'),
        'password' => env('TEST_PANEL_PASSWORD', 'admin666'),
        'server_ip' => env('TEST_PANEL_SERVER_IP', '158.160.253.217'),
        'inbound_id' => env('TEST_PANEL_INBOUND_ID', 1),
        /** Подпись в Happ (флаг + страна / зона) */
        'happ_label' => env('TEST_PANEL_HAPP_LABEL', '🇷🇺 Тест'),
    ],

    'sale_panels' => [
        [
            'name' => '🇳🇱 Нидерланды',
            'happ_label' => env('SALE_PANEL_1_HAPP_LABEL', '🇳🇱 Нидерланды'),
            'url' => env('SALE_PANEL_1_URL', 'http://158.160.229.195'),
            'username' => env('SALE_PANEL_1_USERNAME', 'admin555'),
            'password' => env('SALE_PANEL_1_PASSWORD', 'admin666'),
            'server_ip' => '158.160.229.195',
        ],
        [
            'name' => '🇫🇷 Франция',
            'happ_label' => env('SALE_PANEL_2_HAPP_LABEL', '🇫🇷 Франция'),
            'url' => env('SALE_PANEL_2_URL', 'http://158.160.249.138'),
            'username' => env('SALE_PANEL_2_USERNAME', 'admin555'),
            'password' => env('SALE_PANEL_2_PASSWORD', 'admin666'),
            'server_ip' => '158.160.249.138',
        ],
    ],

    /**
     * Happ routing profile (happ://routing/onadd/{base64-json}).
     * Важно: geo-файлы должны быть небольшими, иначе Happ/Xray может ругаться на лимит скачивания (50MB).
     */
    'happ_routing' => [
        'enabled' => env('HAPP_ROUTING_ENABLED', true),
        'name' => env('HAPP_ROUTING_NAME', 'AVA · RU DIRECT'),
        // Базовые компактные geo-файлы
        'geoip_url' => env('HAPP_GEOIP_URL', 'https://github.com/Loyalsoldier/v2ray-rules-dat/releases/latest/download/geoip.dat'),
        'geosite_url' => env('HAPP_GEOSITE_URL', 'https://github.com/Loyalsoldier/v2ray-rules-dat/releases/latest/download/geosite.dat'),
        // Вся Россия напрямую (мимо VPN)
        'direct_sites' => [
            'geosite:ru-available-only-inside',
            'geosite:category-ru',
            'domain:tbank.ru',
            'domain:tinkoff.ru',
        ],
        'direct_ip' => [
            'geoip:ru',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '169.254.0.0/16',
            '224.0.0.0/4',
            '255.255.255.255',
        ],
        // Остальное будет идти через VPN (GlobalProxy=true в профиле)
        'proxy_sites' => [],
        'proxy_ip' => [],
    ],
];
