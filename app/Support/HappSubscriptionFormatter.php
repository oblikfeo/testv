<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class HappSubscriptionFormatter
{
    /**
     * Подпись узла в подписке: бренд + флаг/страна из config (например «AVA 🇳🇱 Нидерланды»).
     */
    public static function happNodeLabel(string $baseLabel): string
    {
        $brand = trim((string) config('admin.happ_brand', 'AVA'));
        $base = trim($baseLabel);
        if ($base === '') {
            return $brand !== '' ? $brand : 'VPN';
        }
        if ($brand !== '' && preg_match('/^'.preg_quote($brand, '/').'\s+/iu', $base)) {
            return $base;
        }

        return trim($brand.' '.$base);
    }

    /**
     * Deeplink для включения роутинга в Happ (передаётся через header `routing` или строкой в подписке).
     * Формат описан в документации Happ: https://www.happ.su/main/dev-docs/routing
     */
    public static function happRoutingDeeplinkFromConfig(): ?string
    {
        $cfg = config('admin.happ_routing');
        if (! is_array($cfg) || empty($cfg['enabled'])) {
            return null;
        }

        $profile = [
            'Name' => (string) ($cfg['name'] ?? 'AVA Routing'),
            'GlobalProxy' => 'true',
            'Geoipurl' => (string) ($cfg['geoip_url'] ?? ''),
            'Geositeurl' => (string) ($cfg['geosite_url'] ?? ''),
            'DirectSites' => array_values(array_map('strval', $cfg['direct_sites'] ?? [])),
            'DirectIp' => array_values(array_map('strval', $cfg['direct_ip'] ?? [])),
            'ProxySites' => array_values(array_map('strval', $cfg['proxy_sites'] ?? [])),
            'ProxyIp' => array_values(array_map('strval', $cfg['proxy_ip'] ?? [])),
            'BlockSites' => [],
            'BlockIp' => [],
            'DomainStrategy' => 'IPIfNonMatch',
            'FakeDNS' => 'false',
        ];

        $json = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($json) || $json === '') {
            return null;
        }

        return 'happ://routing/onadd/'.base64_encode($json);
    }

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
     * Блок Reality: иногда вложен строкой JSON или лежит под другими ключами.
     *
     * @return array<string, mixed>
     */
    public static function normalizeRealitySettings(array $streamSettings): array
    {
        $rs = $streamSettings['realitySettings'] ?? $streamSettings['REALITY_SETTINGS'] ?? null;

        if (is_string($rs) && $rs !== '') {
            $decoded = json_decode($rs, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($rs) ? $rs : [];
    }

    /**
     * Public key Reality: разные версии 3x-ui / Xray.
     * Если publicKey пустой, но есть privateKey (часто NL-панель) — вычисляем pbk (X25519).
     */
    public static function extractRealityPublicKey(array $realitySettings): string
    {
        $deep = self::deepFindPublicKey($realitySettings);
        if ($deep !== '') {
            return $deep;
        }

        $settings = $realitySettings['settings'] ?? null;
        if (is_array($settings)) {
            $pk = $settings['publicKey'] ?? $settings['public_key'] ?? '';
            if (is_string($pk) && trim($pk) !== '') {
                return trim($pk);
            }
        }

        $pk = $realitySettings['publicKey'] ?? $realitySettings['public_key'] ?? '';
        if (is_string($pk) && trim($pk) !== '') {
            return trim($pk);
        }

        foreach (['privateKey', 'private_key'] as $privKey) {
            $priv = $realitySettings[$privKey] ?? null;
            if (is_string($priv) && $priv !== '') {
                $derived = self::deriveRealityPublicKeyFromPrivateKey($priv);
                if ($derived !== '') {
                    return $derived;
                }
            }
        }

        if (is_array($settings)) {
            foreach (['privateKey', 'private_key'] as $privKey) {
                $priv = $settings[$privKey] ?? null;
                if (is_string($priv) && $priv !== '') {
                    $derived = self::deriveRealityPublicKeyFromPrivateKey($priv);
                    if ($derived !== '') {
                        return $derived;
                    }
                }
            }
        }

        return '';
    }

    /**
     * Декодирование ключа Reality из base64 / base64url (32 байта X25519).
     */
    protected static function decodeRealityKeyB64(string $b64): ?string
    {
        $t = trim($b64);
        if ($t === '') {
            return null;
        }
        $bin = base64_decode(strtr($t, '-_', '+/'), true);
        if ($bin !== false && strlen($bin) === 32) {
            return $bin;
        }
        $bin = base64_decode($t, true);
        if ($bin !== false && strlen($bin) === 32) {
            return $bin;
        }

        return null;
    }

    /**
     * X25519: публичный ключ клиента (pbk) из приватного ключа inbound (как в Xray Reality).
     */
    protected static function deriveRealityPublicKeyFromPrivateKey(string $privateKeyB64): string
    {
        $raw = self::decodeRealityKeyB64($privateKeyB64);
        if ($raw === null || strlen($raw) !== 32) {
            return '';
        }

        try {
            if (function_exists('sodium_crypto_scalarmult_base')) {
                $pubRaw = sodium_crypto_scalarmult_base($raw);
            } elseif (class_exists(\ParagonIE_Sodium_Core_X25519::class)) {
                $pubRaw = \ParagonIE_Sodium_Core_X25519::crypto_scalarmult_curve25519_ref10_base($raw);
            } else {
                return '';
            }

            return rtrim(strtr(base64_encode($pubRaw), '+/', '-_'), '=');
        } catch (\Throwable $e) {
            Log::warning('HappSubscriptionFormatter: не удалось вычислить pbk из privateKey', [
                'message' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * @param  array<string, mixed>  $node
     */
    protected static function deepFindPublicKey(array $node): string
    {
        foreach (['publicKey', 'public_key'] as $key) {
            if (isset($node[$key]) && is_string($node[$key])) {
                $v = trim($node[$key]);
                if (strlen($v) > 16) {
                    return $v;
                }
            }
        }

        foreach (['settings', 'realitySettings'] as $child) {
            if (isset($node[$child]) && is_array($node[$child])) {
                $found = self::deepFindPublicKey($node[$child]);
                if ($found !== '') {
                    return $found;
                }
            }
        }

        return '';
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
        $names = $realitySettings['serverNames'] ?? $realitySettings['serverName'] ?? [];
        if (is_string($names) && $names !== '') {
            return [$names];
        }
        if (is_array($names) && $names !== []) {
            return array_values(array_map('strval', $names));
        }

        return ['www.cloudflare.com'];
    }

    /**
     * @return array{0: string, 1: ?string} [vless line or empty, error message for logs]
     */
    public static function vlessLineFromInboundOrError(
        array $inbound,
        string $uuid,
        string $serverIp,
        string $label
    ): array {
        $streamSettings = self::parseStreamSettingsArray($inbound);
        $realitySettings = self::normalizeRealitySettings($streamSettings);

        $serverNames = self::extractServerNames($realitySettings);
        $publicKey = self::extractRealityPublicKey($realitySettings);
        $shortIds = self::extractShortIds($realitySettings);

        if ($uuid === '' || $uuid === '0') {
            Log::warning('HappSubscriptionFormatter: пустой UUID для VLESS', [
                'inbound_id' => $inbound['id'] ?? null,
            ]);
        }

        if ($publicKey === '') {
            $msg = 'Пустой Reality public key (pbk) для inbound '.($inbound['id'] ?? '?').' — в панели открой inbound → stream: проверь Reality (dest, public key, shortId).';
            Log::error('HappSubscriptionFormatter: '.$msg, [
                'inbound_id' => $inbound['id'] ?? null,
                'remark' => $inbound['remark'] ?? null,
                'security' => $streamSettings['security'] ?? null,
            ]);

            return ['', $msg];
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

        $line = sprintf(
            'vless://%s@%s:%d?%s#%s',
            $uuid,
            $serverIp,
            $port,
            $query,
            rawurlencode($label)
        );

        return [$line, null];
    }

    /**
     * @deprecated use vlessLineFromInboundOrError
     */
    public static function vlessLineFromInbound(
        array $inbound,
        string $uuid,
        string $serverIp,
        string $label
    ): string {
        [$line, ] = self::vlessLineFromInboundOrError($inbound, $uuid, $serverIp, $label);

        return $line;
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
