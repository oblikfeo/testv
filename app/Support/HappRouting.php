<?php

namespace App\Support;

class HappRouting
{
    public const ROUTING_OFF = 'happ://routing/off';

    /**
     * Строка для header/body подписки: профиль роутинга или отключение split-tunnel.
     *
     * @see https://www.happ.su/main/dev-docs/routing
     */
    public static function subscriptionLine(): ?string
    {
        $mode = (string) config('happ_routing.mode', 'split');

        if ($mode === 'off' || ! config('happ_routing.enabled')) {
            return self::ROUTING_OFF;
        }

        return self::deeplink();
    }

    public static function deeplink(): ?string
    {
        $cfg = config('happ_routing');
        if (! is_array($cfg)) {
            return null;
        }

        $mode = (string) ($cfg['mode'] ?? 'split');
        $fullTunnel = $mode === 'full_tunnel';

        $useBuiltinGeo = $fullTunnel || ! empty($cfg['use_builtin_geo']);
        $geoipUrl = $useBuiltinGeo ? '' : (string) ($cfg['geoip_url'] ?? '');
        $geositeUrl = $useBuiltinGeo ? '' : (string) ($cfg['geosite_url'] ?? '');

        if ($fullTunnel) {
            $remoteDnsDomain = 'https://cloudflare-dns.com/dns-query';
            $remoteDnsIp = '1.1.1.1';
            $domesticDnsDomain = 'https://dns.google/dns-query';
            $domesticDnsIp = '8.8.8.8';
            $dnsHosts = [
                'cloudflare-dns.com' => '1.1.1.1',
                'dns.google' => '8.8.8.8',
            ];
        } else {
            $remoteDnsDomain = (string) ($cfg['remote_dns_domain'] ?? 'https://8.8.8.8/dns-query');
            $remoteDnsIp = (string) ($cfg['remote_dns_ip'] ?? '8.8.8.8');
            $domesticDnsDomain = (string) ($cfg['domestic_dns_domain'] ?? 'https://77.88.8.8/dns-query');
            $domesticDnsIp = (string) ($cfg['domestic_dns_ip'] ?? '77.88.8.8');
            $dnsHosts = [];
        }

        $profile = [
            'Name' => (string) ($cfg['name'] ?? 'AVA Routing'),
            'GlobalProxy' => 'true',
            'RouteOrder' => $fullTunnel ? 'proxy-direct' : (string) ($cfg['route_order'] ?? 'block-proxy-direct'),
            'UseChunkFiles' => $fullTunnel ? 'false' : (! empty($cfg['use_chunk_files']) ? 'true' : 'false'),
            'Geoipurl' => $geoipUrl,
            'Geositeurl' => $geositeUrl,
            'LastUpdated' => (string) ($cfg['last_updated'] ?? ''),
            'RemoteDNSType' => (string) ($cfg['remote_dns_type'] ?? 'DoH'),
            'RemoteDNSDomain' => $remoteDnsDomain,
            'RemoteDNSIP' => $remoteDnsIp,
            'DomesticDNSType' => (string) ($cfg['domestic_dns_type'] ?? 'DoH'),
            'DomesticDNSDomain' => $domesticDnsDomain,
            'DomesticDNSIP' => $domesticDnsIp,
            'DnsHosts' => $dnsHosts,
            'DirectSites' => $fullTunnel ? [] : array_values(array_map('strval', $cfg['direct_sites'] ?? [])),
            'DirectIp' => $fullTunnel
                ? ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.0/8', '169.254.0.0/16']
                : array_values(array_map('strval', $cfg['direct_ip'] ?? [])),
            'ProxySites' => $fullTunnel ? [] : array_values(array_map('strval', $cfg['proxy_sites'] ?? [])),
            'ProxyIp' => $fullTunnel ? [] : array_values(array_map('strval', $cfg['proxy_ip'] ?? [])),
            'BlockSites' => $fullTunnel ? [] : array_values(array_map('strval', $cfg['block_sites'] ?? [])),
            'BlockIp' => $fullTunnel ? [] : array_values(array_map('strval', $cfg['block_ip'] ?? [])),
            'DomainStrategy' => $fullTunnel ? 'AsIs' : 'IPIfNonMatch',
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

    public static function announcementHeader(): ?string
    {
        $text = trim((string) config('happ_routing.announcement', ''));
        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) > 200) {
            $text = mb_substr($text, 0, 200);
        }

        return 'base64:'.base64_encode($text);
    }

    public static function subscriptionPinEnabled(): bool
    {
        return filter_var(config('happ_routing.subscription_pin', true), FILTER_VALIDATE_BOOL);
    }
}
