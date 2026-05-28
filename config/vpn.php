<?php

return [

    'shared_hy2_uri' => env('SHARED_HY2_URI', 'hy2://oblik:oblik2026nl@195.133.17.68:443?obfs=salamander&obfs-password=fpcP4gm8lxVnLpxyOZ0eBstrBxkX4h0&pinSHA256=4A:F4:C7:1B:23:8D:0E:F7:E5:DA:E8:70:2E:19:3E:D0:D8:54:07:42:25:80:E6:D5:BD:92:BC:9C:3A:44:6F:EF&insecure=1#IPv4'),

    'shared_vless_uri' => env('SHARED_VLESS_URI', ''),

    'trial' => [
        'duration_hours' => (int) env('TRIAL_DURATION_HOURS', 3),
        'soft_quota_gb' => (int) env('TRIAL_SOFT_QUOTA_GB', 0),
    ],

];
