<?php

return [
    'login' => env('ADMIN_LOGIN', ''),
    'password' => env('ADMIN_PASSWORD', ''),

    'trial' => [
        'duration_hours' => (int) env('TRIAL_DURATION_HOURS', 3),
        'soft_quota_gb' => (int) env('TRIAL_SOFT_QUOTA_GB', 5),
    ],
];
