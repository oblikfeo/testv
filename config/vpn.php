<?php

return [

    'shared_hy2_uri' => env('SHARED_HY2_URI', 'vless://902c8050-10a1-4405-881c-1f6055400d28@169.40.15.223:443?type=tcp&security=reality&sni=www.microsoft.com&fp=chrome&pbk=EdzPHpQEArSM18Z2mhiQ0IQcE7CSRud5JDPmeSH2l3k&sid=a1b2c3d4&spx=%2F&flow=xtls-rprx-vision#223'),

    'shared_vless_uri' => env('SHARED_VLESS_URI', ''),

    // CDN xhttp (белые списки операторов) — последний узел в подписке.
    'shared_cdn_uri' => env('SHARED_CDN_URI', ''),

    'trial' => [
        'duration_hours' => (int) env('TRIAL_DURATION_HOURS', 3),
        'soft_quota_gb' => (int) env('TRIAL_SOFT_QUOTA_GB', 0),
    ],

];
