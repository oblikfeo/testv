<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class HappSubscriptionFormatter
{
    /**
     * Разбор streamSettings: в API 3x-ui бывает и строка JSON, и уже массив.
     * Если передать массив в json_decode — получим null и пустой pbk → в Happ/Xray ошибка «empty password».
     *
     * @return array<string, mixed>
     */
    public static function parseStreamSettingsArray(array $inbound): array
    {
        $raw = $inbound['streamSettings'] ?? null;
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Public key Reality: разные версии панелей кладут ключ в разные поля.
     */
    public static function extractRealityPublicKey(array $realitySettings): string
    {
        $settings = $realitySettings['settings'] ?? null;
        if (is_array($settings)) {
            $pk = $settings['publicKey'] ?? '';
            if ($pk !== '') {
                return (string) $pk;
            }
        }

        return (string) ($realitySettings['publicKey'] ?? '');
    }

    /**
     * @return list<string>
     */
    public static function extractShortIds(array $realitySettings): array
    {
        if (! empty($realitySettings['shortIds']) && is_array($realitySettings['shortIds'])) {
            return array_values(array_map('strval', $realitySettings['shortIds']));
        }
        if (isset($realitySettings['shortId']) && $realitySettings['shortId'] !== '') {
            return [(string) $realitySettings['shortId']];
        }

        return [''];
    }

    /**
     * @return list<string>
     */
    public static function extractServerNames(array $realitySettings): array
    {
        $names = $realitySettings['serverNames'] ?? [];
        if (is_array($names) && $names !== []) {
            return array_values(array_map('strval', $names));
        }

        return ['www.cloudflare.com'];
    }

    public static function vlessLineFromInbound(
        array $inbound,
        string $uuid,
        string $serverIp,
        string $label
    ): string {
        $streamSettings = self::parseStreamSettingsArray($inbound);
        $realitySettings = $streamSettings['realitySettings'] ?? [];

        $serverNames = self::extractServerNames(is_array($realitySettings) ? $realitySettings : []);
        $publicKey = self::extractRealityPublicKey(is_array($realitySettings) ? $realitySettings : []);
        $shortIds = self::extractShortIds(is_array($realitySettings) ? $realitySettings : []);

        if ($uuid === '' || $uuid === '0') {
            Log::warning('HappSubscriptionFormatter: пустой UUID для VLESS', [
                'inbound_id' => $inbound['id'] ?? null,
            ]);
        }

        if ($publicKey === '') {
            Log::warning('HappSubscriptionFormatter: пустой Reality publicKey (pbk) — проверьте inbound на панели', [
                'inbound_id' => $inbound['id'] ?? null,
                'remark' => $inbound['remark'] ?? null,
            ]);
        }

        $port = (int) ($inbound['port'] ?? 443);

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

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return sprintf(
            'vless://%s@%s:%d?%s#%s',
            $uuid,
            $serverIp,
            $port,
            $query,
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
