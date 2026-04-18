<?php

return [
    'login' => env('ADMIN_LOGIN', 'admin'),
    'password' => env('ADMIN_PASSWORD', 'secret'),

    'test_panel' => [
        'url' => env('TEST_PANEL_URL', 'http://158.160.253.217'),
        'username' => env('TEST_PANEL_USERNAME', 'admin555'),
        'password' => env('TEST_PANEL_PASSWORD', 'admin666'),
        'server_ip' => env('TEST_PANEL_SERVER_IP', '158.160.253.217'),
        'inbound_id' => env('TEST_PANEL_INBOUND_ID', 1),
    ],

    'sale_panels' => [
        [
            'name' => 'Связка 1 (NL)',
            'url' => env('SALE_PANEL_1_URL', 'http://158.160.229.195'),
            'username' => env('SALE_PANEL_1_USERNAME', 'admin555'),
            'password' => env('SALE_PANEL_1_PASSWORD', 'admin666'),
            'server_ip' => '158.160.229.195',
        ],
        [
            'name' => 'Связка 2 (FR)',
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
        'DOMAIN-SUFFIX,tbank.ru,DIRECT',
        'DOMAIN-SUFFIX,tinkoff.ru,DIRECT',
        'DOMAIN-SUFFIX,cloudfront.net,DIRECT',
        'DOMAIN-SUFFIX,amazonaws.com,DIRECT',
    ],
];
