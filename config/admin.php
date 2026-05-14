<?php

return [
    'login' => env('ADMIN_LOGIN', ''),
    'password' => env('ADMIN_PASSWORD', ''),

    /** Префикс имени узла в Happ: «AVA 🇳🇱 Нидерланды» */
    'happ_brand' => env('HAPP_BRAND', 'AVA'),

    /**
     * Если у активного розничного тарифа в БД traffic_gb = 0, для подписки показывается
     * этот объём (информационно, в subscription-userinfo). 0 = безлимит.
     */
    'default_retail_traffic_gb' => (int) env('DEFAULT_RETAIL_TRAFFIC_GB', 100),

    /**
     * Общие параметры VLESS Reality / Hysteria2, одинаковые на всех узлах.
     * Один UUID и один Hysteria-пароль для всей инфры — модель «общая подписка для всех».
     * Контроль срока подписки на стороне сайта (/sub/{sub_id} отдаёт 403 после expires_at).
     */
    'shared' => [
        'vless_uuid' => env('SHARED_VLESS_UUID', ''),
        'reality_pbk' => env('SHARED_REALITY_PBK', ''),
        'reality_sid' => env('SHARED_REALITY_SID', ''),
        'reality_sni' => env('SHARED_REALITY_SNI', 'www.cloudflare.com'),
        'reality_fp' => env('SHARED_REALITY_FP', 'chrome'),
        'reality_flow' => env('SHARED_REALITY_FLOW', 'xtls-rprx-vision'),
        'hysteria_password' => env('SHARED_HYSTERIA_PASSWORD', ''),
        'hysteria_obfs_password' => env('SHARED_HYSTERIA_OBFS_PASSWORD', ''),
        'hysteria_obfs' => env('SHARED_HYSTERIA_OBFS', 'salamander'),
        'hysteria_sni' => env('SHARED_HYSTERIA_SNI', env('SHARED_REALITY_SNI', 'www.cloudflare.com')),
        // 1 -- клиент пропускает self-signed TLS (нужно для cert'ов под IP). 0 -- строгий чек.
        'hysteria_insecure' => (int) env('SHARED_HYSTERIA_INSECURE', 1),
    ],

    /**
     * Endpoint'ы подписки. Каждый элемент — одна строка в фиде Happ.
     * type = vless | hysteria2. Имя узла берётся из happ_brand + happ_label.
     */
    'endpoints' => [
        [
            'key' => 'vless-yandex-nl',
            'type' => 'vless',
            'happ_label' => env('NODE_A_HAPP_LABEL', '🇳🇱 Нидерланды'),
            'host' => env('NODE_A_HOST', '158.160.229.195'),
            'port' => (int) env('NODE_A_VLESS_PORT', 443),
        ],
        [
            'key' => 'vless-nl',
            'type' => 'vless',
            'happ_label' => env('NODE_C_HAPP_LABEL', '🇳🇱 Нидерланды Direct'),
            'host' => env('NODE_C_HOST', '82.23.162.45'),
            'port' => (int) env('NODE_C_VLESS_PORT', 443),
        ],
        [
            'key' => 'hy-fr',
            'type' => 'hysteria2',
            'happ_label' => env('NODE_D_HAPP_LABEL', '🇫🇷 Франция'),
            'host' => env('NODE_D_HOST', '82.22.50.114'),
            'port' => (int) env('NODE_D_HYSTERIA_PORT', 8443),
        ],
        [
            'key' => 'vless-new',
            'type' => 'vless',
            'happ_label' => env('NODE_E_VLESS_HAPP_LABEL', '🌐 Резерв VLESS'),
            'host' => env('NODE_E_HOST', '185.214.108.78'),
            'port' => (int) env('NODE_E_VLESS_PORT', 443),
        ],
        [
            'key' => 'hy-new',
            'type' => 'hysteria2',
            'happ_label' => env('NODE_E_HYSTERIA_HAPP_LABEL', '🌐 Резерв Hysteria'),
            'host' => env('NODE_E_HOST', '185.214.108.78'),
            'port' => (int) env('NODE_E_HYSTERIA_PORT', 8443),
        ],
    ],

    /**
     * Профиль триала: показываем «осталось 5 ГБ» в subscription-userinfo (soft-лимит, не блочим),
     * а по таймеру 3 часа /sub/{sub_id} отдаёт 403.
     */
    'trial' => [
        'duration_hours' => (int) env('TRIAL_DURATION_HOURS', 3),
        'soft_quota_gb' => (int) env('TRIAL_SOFT_QUOTA_GB', 5),
    ],

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
