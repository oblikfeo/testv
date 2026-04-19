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
     * Доп. строки в конце тела подписки (Clash-подобный формат для Happ и др.).
     * Прямой доступ к банкам/CDN без VPN.
     */
    'happ_routing_rules' => [
        /**
         * Россия: списки заблокированных доменов/подсетей (ru-blocked) из runetfreedom/russia-v2ray-rules-dat.
         * Категории: GEOIP ru-blocked, GEOSITE ru-blocked.
         */
        'GEOSITE,ru-blocked,PROXY',
        'GEOIP,ru-blocked,PROXY',
        'DOMAIN-SUFFIX,tbank.ru,DIRECT',
        'DOMAIN-SUFFIX,tinkoff.ru,DIRECT',
        'DOMAIN-SUFFIX,cloudfront.net,DIRECT',
        'DOMAIN-SUFFIX,amazonaws.com,DIRECT',
    ],
];
