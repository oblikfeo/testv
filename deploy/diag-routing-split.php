#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['happ_routing.mode' => 'split']);
$d = json_decode(base64_decode(substr(App\Support\HappRouting::deeplink(), 22)), true);

foreach (['Name', 'GlobalProxy', 'RouteOrder', 'UseChunkFiles', 'Geoipurl', 'Geositeurl', 'DomainStrategy'] as $k) {
    echo "$k=" . ($d[$k] ?? '') . PHP_EOL;
}
echo 'DirectSites=' . count($d['DirectSites'] ?? []) . PHP_EOL;
echo 'ProxySites=' . count($d['ProxySites'] ?? []) . PHP_EOL;
echo 'DirectSites: ' . implode(', ', $d['DirectSites'] ?? []) . PHP_EOL;
echo 'ProxySites: ' . implode(', ', $d['ProxySites'] ?? []) . PHP_EOL;
