<?php

return [
    'login' => env('ADMIN_LOGIN', 'admin'),
    'password' => env('ADMIN_PASSWORD', 'secret'),

    /** Префикс имени узла в Happ: «AVA 🇳🇱 Нидерланды» */
    'happ_brand' => env('HAPP_BRAND', 'AVA'),

    /**
     * Если у активного розничного тарифа в БД traffic_gb = 0, на панель уходит totalGB=0 (безлимит).
     * Подставка ГБ для панели (sponsor / admin-friends с 0 не трогаем). 0 = не подставлять.
     */
    'default_retail_traffic_gb' => (int) env('DEFAULT_RETAIL_TRAFFIC_GB', 100),

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
            'server_ip' => env('SALE_PANEL_1_SERVER_IP', '158.160.229.195'),
        ],
        [
            'name' => '🇫🇷 Франция',
            'happ_label' => env('SALE_PANEL_2_HAPP_LABEL', '🇫🇷 Франция'),
            'url' => env('SALE_PANEL_2_URL', 'http://158.160.249.138'),
            'username' => env('SALE_PANEL_2_USERNAME', 'admin555'),
            'password' => env('SALE_PANEL_2_PASSWORD', 'admin666'),
            'server_ip' => env('SALE_PANEL_2_SERVER_IP', '158.160.249.138'),
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
        // Как у конкурентов: geoip.dat + dlc.dat
        'geoip_url' => env('HAPP_GEOIP_URL', 'https://github.com/v2fly/geoip/releases/latest/download/geoip.dat'),
        'geosite_url' => env('HAPP_GEOSITE_URL', 'https://github.com/v2fly/domain-list-community/releases/latest/download/dlc.dat'),
        // Вся Россия напрямую (мимо VPN)
        // Только категории, которые есть в v2fly/dlc.dat
        'direct_sites' => [
            'geosite:category-gov-ru',
            'geosite:yandex',
            'geosite:mailru',
            'geosite:vk',
            'domain:tbank.ru',
            'domain:tinkoff.ru',
            'domain:gosuslugi.ru',
            'domain:nalog.gov.ru',
            'domain:mos.ru',
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
