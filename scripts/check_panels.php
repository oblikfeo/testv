<?php

/**
 * Проверка панелей 3x-ui: inbound + извлечение pbk (в т.ч. из privateKey).
 * Запуск: php scripts/check_panels.php (берёт URL/логины из .env).
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$x = $app->make(\App\Services\XuiApiService::class);

$panels = [
    ['NL', env('SALE_PANEL_1_URL'), env('SALE_PANEL_1_USERNAME'), env('SALE_PANEL_1_PASSWORD')],
    ['FR', env('SALE_PANEL_2_URL'), env('SALE_PANEL_2_USERNAME'), env('SALE_PANEL_2_PASSWORD')],
    ['TEST', env('TEST_PANEL_URL'), env('TEST_PANEL_USERNAME'), env('TEST_PANEL_PASSWORD')],
];

foreach ($panels as [$tag, $url, $u, $p]) {
    $r = $x->getInbounds($url, $u, $p);
    $ok = ! empty($r['obj']);
    echo $tag.': ';
    if (! $ok) {
        echo 'FAIL (no inbounds) msg='.($r['msg'] ?? json_encode($r))."\n";
        continue;
    }
    echo 'inbounds='.count($r['obj']);
    $i0 = $r['obj'][0];
    $raw = $i0['streamSettings'] ?? null;
    $ss = is_array($raw) ? $raw : json_decode((string) $raw, true);
    $ss = is_array($ss) ? $ss : [];
    $pbk = \App\Support\HappSubscriptionFormatter::extractRealityPublicKey(
        \App\Support\HappSubscriptionFormatter::normalizeRealitySettings($ss)
    );
    echo ' inbound[0] id='.($i0['id'] ?? '?').' port='.($i0['port'] ?? '?');
    echo ' security='.($ss['security'] ?? '?');
    echo ' pbk_len='.strlen($pbk).($pbk !== '' ? ' OK' : ' EMPTY');
    echo "\n";
}
