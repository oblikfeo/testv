<?php

return [
    'login' => env('ADMIN_LOGIN', ''),
    'password' => env('ADMIN_PASSWORD', ''),

    /** Префикс имени узла в Happ: «AVA 🇳🇱 Нидерланды» */
    'happ_brand' => env('HAPP_BRAND', 'AVA'),

    /**
     * Если у активного розничного тарифа в БД traffic_gb = 0, на панель уходит totalGB=0 (безлимит).
     * Подставка ГБ для панели (sponsor / admin-friends с 0 не трогаем). 0 = не подставлять.
     */
    'default_retail_traffic_gb' => (int) env('DEFAULT_RETAIL_TRAFFIC_GB', 100),

    'test_panel' => [
        'url' => env('TEST_PANEL_URL', ''),
        'username' => env('TEST_PANEL_USERNAME', ''),
        'password' => env('TEST_PANEL_PASSWORD', ''),
        'server_ip' => env('TEST_PANEL_SERVER_IP', ''),
        'inbound_id' => env('TEST_PANEL_INBOUND_ID', 1),
        /** Подпись в Happ (флаг + страна / зона) */
        'happ_label' => env('TEST_PANEL_HAPP_LABEL', '🇷🇺 Тест'),
    ],

    'sale_panels' => [
        [
            'name' => '🇳🇱 Нидерланды',
            'happ_label' => env('SALE_PANEL_1_HAPP_LABEL', '🇳🇱 Нидерланды'),
            'url' => env('SALE_PANEL_1_URL', ''),
            'username' => env('SALE_PANEL_1_USERNAME', ''),
            'password' => env('SALE_PANEL_1_PASSWORD', ''),
            'server_ip' => env('SALE_PANEL_1_SERVER_IP', ''),
        ],
        [
            'name' => '🇫🇷 Франция',
            'happ_label' => env('SALE_PANEL_2_HAPP_LABEL', '🇫🇷 Франция'),
            'url' => env('SALE_PANEL_2_URL', ''),
            'username' => env('SALE_PANEL_2_USERNAME', ''),
            'password' => env('SALE_PANEL_2_PASSWORD', ''),
            'server_ip' => env('SALE_PANEL_2_SERVER_IP', ''),
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
        // Вся Россия напрямую (мимо VPN). Сюда — всё, что отказывает обслуживать иностранный IP:
        // банки, госуслуги, маркетплейсы, такси/доставка, видеосервисы, музыка.
        // Категории geosite:* — только те, что точно есть в v2fly/dlc.dat (иначе Happ/Xray ругнётся).
        // 'domain:foo.ru' в Xray-формате матчит и foo.ru, и любой *.foo.ru.
        'direct_sites' => [
            // RU geo-категории
            'geosite:category-gov-ru',
            'geosite:yandex',
            'geosite:mailru',
            'geosite:vk',

            // Банки
            'domain:tbank.ru',
            'domain:tinkoff.ru',
            'domain:tcsbank.ru',
            'domain:sberbank.ru',
            'domain:sber.ru',
            'domain:sberbank.com',
            'domain:online.sberbank.ru',
            'domain:vtb.ru',
            'domain:alfabank.ru',
            'domain:alfabank.com',
            'domain:open.ru',
            'domain:psbank.ru',
            'domain:rshb.ru',
            'domain:gazprombank.ru',
            'domain:raiffeisen.ru',
            'domain:rosbank.ru',
            'domain:sovcombank.ru',
            'domain:mkb.ru',
            'domain:uralsib.ru',
            'domain:akbars.ru',
            'domain:pochtabank.ru',
            'domain:cbr.ru',

            // СБП и платёжные
            'domain:nspk.ru',
            'domain:sbp.nspk.ru',
            'domain:mironline.ru',

            // Госуслуги, налоги, госорганы
            'domain:gosuslugi.ru',
            'domain:nalog.gov.ru',
            'domain:nalog.ru',
            'domain:mos.ru',
            'domain:pfr.gov.ru',
            'domain:sfr.gov.ru',
            'domain:fssp.gov.ru',
            'domain:roskazna.gov.ru',
            'domain:mvd.ru',
            'domain:gibdd.ru',

            // Маркетплейсы / e-commerce
            'domain:ozon.ru',
            'domain:wildberries.ru',
            'domain:wb.ru',
            'domain:aliexpress.ru',
            'domain:market.yandex.ru',
            'domain:megamarket.ru',
            'domain:lamoda.ru',
            'domain:dns-shop.ru',
            'domain:mvideo.ru',
            'domain:eldorado.ru',
            'domain:citilink.ru',

            // Объявления / агрегаторы
            'domain:avito.ru',
            'domain:cian.ru',
            'domain:youla.ru',
            'domain:auto.ru',
            'domain:drom.ru',

            // Доставка / еда / такси
            'domain:delivery-club.ru',
            'domain:samokat.ru',
            'domain:perekrestok.ru',
            'domain:vkusvill.ru',
            'domain:lenta.com',
            'domain:magnit.ru',

            // Видео / музыка / стриминг РФ
            'domain:kinopoisk.ru',
            'domain:ivi.ru',
            'domain:wink.ru',
            'domain:okko.tv',
            'domain:more.tv',
            'domain:start.ru',
            'domain:premier.one',
            'domain:rutube.ru',
            'domain:zvuk.com',
            'domain:music.yandex.ru',

            // Связь / провайдеры
            'domain:beeline.ru',
            'domain:megafon.ru',
            'domain:mts.ru',
            'domain:tele2.ru',
            'domain:rt.ru',
            'domain:rostelecom.ru',
            'domain:dom.ru',

            // Карты / навигация / другие важные RU-сервисы
            'domain:2gis.ru',
            'domain:2gis.com',
            'domain:dzen.ru',

            // Локальные FQDN (mDNS / роутеры) — нельзя гнать в туннель
            'domain:lan',
            'domain:local',
            'domain:home',
            'domain:internal',
        ],
        'direct_ip' => [
            // Весь RU гео — даже если домен не сматчился, IP с RU-префиксом идёт DIRECT.
            // Это страховка для приложений, которые ходят по hardcoded IP без DNS (банки, СБП).
            'geoip:ru',
            // Приватные / служебные / loopback / link-local / multicast / broadcast
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
        // Остальное будет идти через VPN (GlobalProxy=true в профиле)
        'proxy_sites' => [],
        'proxy_ip' => [],
    ],
];
