<?php

namespace App\Support;

class HappSubscriptionFormatter
{
    public static function vlessLineFromInbound(
        array $inbound,
        string $uuid,
        string $serverIp,
        string $label
    ): string {
        $streamSettings = json_decode($inbound['streamSettings'], true) ?? [];
        $realitySettings = $streamSettings['realitySettings'] ?? [];

        $serverNames = $realitySettings['serverNames'] ?? ['www.cloudflare.com'];
        $publicKey = $realitySettings['settings']['publicKey'] ?? '';
        $shortIds = $realitySettings['shortIds'] ?? [''];

        $port = $inbound['port'];

        $params = [
            'type' => 'tcp',
            'security' => 'reality',
            'encryption' => 'none',
            'fp' => 'chrome',
            'pbk' => $publicKey,
            'sid' => $shortIds[0] ?? '',
            'sni' => $serverNames[0] ?? 'www.cloudflare.com',
            'flow' => 'xtls-rprx-vision',
        ];

        return sprintf(
            'vless://%s@%s:%d?%s#%s',
            $uuid,
            $serverIp,
            $port,
            http_build_query($params),
            rawurlencode($label)
        );
    }

    public static function buildUserInfo(int $upload, int $download, int $total, int $expireTs): string
    {
        return sprintf(
            'upload=%d; download=%d; total=%d; expire=%d',
            $upload,
            $download,
            $total,
            $expireTs
        );
    }
}
