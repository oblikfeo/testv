<?php

namespace App\Services;

use App\Models\SaleKey;
use App\Models\TrialKey;
use App\Support\HappSubscriptionFormatter;

/**
 * Сборка единого статического фида подписки для Happ / совместимых клиентов.
 *
 * Модель «одна подписка для всех»: контент фида одинаков для всех активных подписчиков,
 * параметры VLESS/Hysteria берутся из config('admin.shared') и config('admin.endpoints').
 * Персонализирован только заголовок `subscription-userinfo` — лимиты/срок конкретного ключа.
 */
class SubscriptionFeedBuilder
{
    public function buildForTrial(TrialKey $trialKey): array
    {
        if (! $trialKey->isActive()) {
            return ['error' => 'Пробный период недоступен', 'code' => 403];
        }

        $body = $this->buildPublicBody();
        if ($body === '') {
            return ['error' => 'Подписка не сконфигурирована (admin.endpoints пуст)', 'code' => 500];
        }

        $userInfo = HappSubscriptionFormatter::buildUserInfo(
            0,
            (int) $trialKey->used_bytes,
            (int) $trialKey->total_bytes,
            $trialKey->expires_at->timestamp
        );

        return [
            'body' => $body,
            'user_info' => $userInfo,
            'profile_title' => 'AVA тестовый период',
        ];
    }

    public function buildForSale(SaleKey $saleKey): array
    {
        if ($saleKey->status !== 'active' || $saleKey->isExpired()) {
            return ['error' => 'Подписка не активна', 'code' => 403];
        }

        if (! $saleKey->subscription?->isActive()) {
            return ['error' => 'Подписка не активна', 'code' => 403];
        }

        $body = $this->buildPublicBody();
        if ($body === '') {
            return ['error' => 'Подписка не сконфигурирована (admin.endpoints пуст)', 'code' => 500];
        }

        $total = $saleKey->total_bytes > 0 ? (int) $saleKey->total_bytes : 0;
        $userInfo = HappSubscriptionFormatter::buildUserInfo(
            0,
            (int) $saleKey->used_bytes,
            $total,
            $saleKey->expires_at->timestamp
        );

        return [
            'body' => $body,
            'user_info' => $userInfo,
            'profile_title' => 'AVA VPN',
        ];
    }

    /**
     * Тело публичного фида: одна строка на каждый endpoint в config('admin.endpoints').
     * Поддерживаемые типы: vless (Reality), hysteria2.
     */
    public function buildPublicBody(): string
    {
        $endpoints = (array) config('admin.endpoints', []);
        if ($endpoints === []) {
            return '';
        }

        $lines = [];
        foreach ($endpoints as $ep) {
            $line = $this->endpointLine((array) $ep);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $endpoint
     */
    protected function endpointLine(array $endpoint): string
    {
        $type = (string) ($endpoint['type'] ?? '');
        $host = (string) ($endpoint['host'] ?? '');
        $port = (int) ($endpoint['port'] ?? 0);
        $label = HappSubscriptionFormatter::happNodeLabel(
            (string) ($endpoint['happ_label'] ?? '')
        );

        if ($host === '' || $port <= 0) {
            return '';
        }

        return match ($type) {
            'vless' => $this->vlessLine($host, $port, $label),
            'hysteria2', 'hy2', 'hysteria' => $this->hysteriaLine($host, $port, $label),
            default => '',
        };
    }

    protected function vlessLine(string $host, int $port, string $label): string
    {
        $shared = (array) config('admin.shared', []);
        $uuid = (string) ($shared['vless_uuid'] ?? '');
        $pbk = (string) ($shared['reality_pbk'] ?? '');
        $sid = (string) ($shared['reality_sid'] ?? '');
        $sni = (string) ($shared['reality_sni'] ?? 'www.cloudflare.com');
        $fp = (string) ($shared['reality_fp'] ?? 'chrome');
        $flow = (string) ($shared['reality_flow'] ?? 'xtls-rprx-vision');

        if ($uuid === '' || $pbk === '') {
            return '';
        }

        $params = [
            'type' => 'tcp',
            'security' => 'reality',
            'encryption' => 'none',
            'fp' => $fp,
            'pbk' => $pbk,
            'sid' => $sid,
            'sni' => $sni,
            'flow' => $flow,
        ];

        return sprintf(
            'vless://%s@%s:%d?%s#%s',
            $uuid,
            $host,
            $port,
            http_build_query($params, '', '&', PHP_QUERY_RFC3986),
            rawurlencode($label)
        );
    }

    protected function hysteriaLine(string $host, int $port, string $label): string
    {
        $shared = (array) config('admin.shared', []);
        $password = (string) ($shared['hysteria_password'] ?? '');
        $obfs = (string) ($shared['hysteria_obfs'] ?? 'salamander');
        $obfsPassword = (string) ($shared['hysteria_obfs_password'] ?? '');
        $sni = (string) ($shared['reality_sni'] ?? 'www.cloudflare.com');

        if ($password === '') {
            return '';
        }

        $params = [
            'sni' => $sni,
            'insecure' => '0',
        ];
        if ($obfsPassword !== '') {
            $params['obfs'] = $obfs;
            $params['obfs-password'] = $obfsPassword;
        }

        return sprintf(
            'hysteria2://%s@%s:%d/?%s#%s',
            rawurlencode($password),
            $host,
            $port,
            http_build_query($params, '', '&', PHP_QUERY_RFC3986),
            rawurlencode($label)
        );
    }
}
