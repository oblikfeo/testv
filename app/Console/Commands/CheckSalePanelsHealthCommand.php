<?php

namespace App\Console\Commands;

use App\Services\XuiApiService;
use App\Support\HappSubscriptionFormatter;
use Illuminate\Console\Command;

/**
 * Проверка API 3x-ui и TCP :443 на публичных IP панелей (розница + тестовая).
 */
class CheckSalePanelsHealthCommand extends Command
{
    protected $signature = 'vpn:check-panels';

    protected $description = 'Проверить доступность панелей 3x-ui и порта 443 на server_ip';

    public function handle(XuiApiService $xui): int
    {
        $rows = [];

        foreach (config('admin.sale_panels', []) as $idx => $panel) {
            $tag = 'SALE #'.($idx + 1).' '.($panel['name'] ?? '');
            $rows[] = $this->probeRow($xui, $tag, $panel);
        }

        $test = config('admin.test_panel', []);
        if (! empty($test['url'])) {
            $rows[] = $this->probeRow($xui, 'TEST', [
                'url' => $test['url'],
                'username' => $test['username'] ?? '',
                'password' => $test['password'] ?? '',
                'server_ip' => $test['server_ip'] ?? '',
            ]);
        }

        $this->table(['Панель', 'API', 'TCP :443', 'inbound[0]', 'pbk'], $rows);

        $bad = array_filter($rows, fn (array $r) => str_starts_with($r[1], 'FAIL') || str_starts_with($r[2], 'FAIL'));

        if ($bad !== []) {
            $this->warn('Есть ошибки: проверьте x-ui/xray на ВМ, security group Yandex, туннель до целевого IP (см. админку «схема серверов»).');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}
     */
    protected function probeRow(XuiApiService $xui, string $tag, array $panel): array
    {
        $url = (string) ($panel['url'] ?? '');
        $ip = (string) ($panel['server_ip'] ?? '');

        try {
            $inbounds = $xui->getInbounds($url, (string) ($panel['username'] ?? ''), (string) ($panel['password'] ?? ''));
        } catch (\Throwable $e) {
            return [$tag, 'FAIL: '.$e->getMessage(), '—', '—', '—'];
        }

        if (empty($inbounds['obj'])) {
            return [$tag, 'FAIL: '.($inbounds['msg'] ?? 'no inbounds'), '—', '—', '—'];
        }

        $i0 = $inbounds['obj'][0];
        $raw = $i0['streamSettings'] ?? null;
        $ss = is_array($raw) ? $raw : json_decode((string) $raw, true);
        $ss = is_array($ss) ? $ss : [];
        $pbk = HappSubscriptionFormatter::extractRealityPublicKey(
            HappSubscriptionFormatter::normalizeRealitySettings($ss)
        );
        $pbkOk = $pbk !== '' ? 'len '.strlen($pbk) : 'EMPTY';

        $apiOk = 'OK inb='.count($inbounds['obj']);

        if ($ip === '' || ! filter_var($ip, FILTER_VALIDATE_IP)) {
            return [$tag, $apiOk, 'SKIP (no server_ip)', (string) ($i0['id'] ?? '?'), $pbkOk];
        }

        $tcp = $this->tcpCheck($ip, 443) ? 'OK' : 'FAIL timeout';

        return [$tag, $apiOk, $tcp, (string) ($i0['id'] ?? '?'), $pbkOk];
    }

    protected function tcpCheck(string $host, int $port, float $timeout = 4.0): bool
    {
        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (! is_resource($fp)) {
            return false;
        }
        fclose($fp);

        return true;
    }
}
