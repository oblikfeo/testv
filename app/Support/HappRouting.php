<?php

namespace App\Support;

class HappRouting
{
    /**
     * Deeplink для роутинга Happ (header `routing` или первая строка подписки).
     *
     * @see https://www.happ.su/main/dev-docs/routing
     */
    public static function deeplink(): ?string
    {
        $cfg = config('happ_routing');
        if (! is_array($cfg) || empty($cfg['enabled'])) {
            return null;
        }

        $useBuiltinGeo = ! empty($cfg['use_builtin_geo']);
        $geoipUrl = $useBuiltinGeo ? '' : (string) ($cfg['geoip_url'] ?? '');
        $geositeUrl = $useBuiltinGeo ? '' : (string) ($cfg['geosite_url'] ?? '');

        $profile = [
            'Name' => (string) ($cfg['name'] ?? 'AVA Routing'),
            'GlobalProxy' => 'true',
            'UseChunkFiles' => ! empty($cfg['use_chunk_files']) ? 'true' : 'false',
            'Geoipurl' => $geoipUrl,
            'Geositeurl' => $geositeUrl,
            'LastUpdated' => (string) ($cfg['last_updated'] ?? ''),
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

    public static function isVpnClient(string $userAgent): bool
    {
        $clients = ['happ', 'hiddify', 'v2rayn', 'v2rayng', 'streisand', 'shadowrocket', 'quantumult', 'clash', 'v2raytun'];
        $ua = strtolower($userAgent);

        foreach ($clients as $client) {
            if (str_contains($ua, $client)) {
                return true;
            }
        }

        return false;
    }
}
