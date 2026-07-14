<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$plan = App\Models\Plan::updateOrCreate(
    ['slug' => 'premium-90'],
    [
        'name' => 'Премиум',
        'devices' => 10,
        'days' => 90,
        'price' => 3490,
        'discount' => 0,
        'is_popular' => false,
        'sort_order' => 7,
        'traffic_gb' => 1000,
        'is_active' => true,
    ]
);

echo json_encode(
    $plan->only(['id', 'name', 'slug', 'devices', 'days', 'traffic_gb', 'price', 'is_active']),
    JSON_UNESCAPED_UNICODE
).PHP_EOL;
