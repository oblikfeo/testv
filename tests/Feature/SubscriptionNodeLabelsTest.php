<?php

namespace Tests\Feature;

use App\Support\SharedVpnAccess;
use Tests\TestCase;

class SubscriptionNodeLabelsTest extends TestCase
{
    /** Реальная ссылка узла «Домашний интернет» (FI, vmess over ws). */
    private const VMESS = 'vmess://eyJ2IjoiMiIsInBzIjoiRkktdm1lc3Mtd3MiLCJhZGQiOiIyLjI3LjQyLjE4NyIsInBvcnQiOiI4MDgwIiwiaWQiOiJjOGM5ZmJiZC0yYjY0LTQ5NmQtODkzZi1lYjRkNDA5OGFmMDQiLCJhaWQiOiIwIiwic2N5IjoiYXV0byIsIm5ldCI6IndzIiwidHlwZSI6Im5vbmUiLCJob3N0IjoiIiwicGF0aCI6Ii80ZWI5MTQ2ODUwIiwidGxzIjoiIn0=';

    private function vmessConfig(string $uri): array
    {
        $payload = substr($uri, strlen('vmess://'));

        return json_decode(base64_decode($payload, true), true);
    }

    public function test_vmess_node_is_renamed_inside_the_base64_config(): void
    {
        config([
            'vpn.shared_home_uri' => self::VMESS,
            'vpn.shared_cellular_uri' => '',
        ]);

        $uris = SharedVpnAccess::namedNodeUris();

        $this->assertCount(1, $uris);
        $this->assertStringStartsWith('vmess://', $uris[0]);
        // Подпись должна оказаться в ps, а не во #fragment: фрагмент у vmess
        // клиент игнорирует и показал бы исходное имя из ссылки.
        $this->assertStringNotContainsString('#', $uris[0]);

        $config = $this->vmessConfig($uris[0]);
        $this->assertSame('🇫🇮 Домашний интернет', $config['ps']);
        $this->assertNotSame('FI-vmess-ws', $config['ps']);
    }

    public function test_vmess_connection_parameters_survive_relabelling(): void
    {
        config(['vpn.shared_home_uri' => self::VMESS, 'vpn.shared_cellular_uri' => '']);

        $original = $this->vmessConfig(self::VMESS);
        $relabelled = $this->vmessConfig(SharedVpnAccess::namedNodeUris()[0]);

        foreach (['v', 'add', 'port', 'id', 'aid', 'scy', 'net', 'type', 'host', 'path', 'tls'] as $field) {
            $this->assertSame($original[$field], $relabelled[$field], "поле {$field} изменилось");
        }
        // Слеш в path не должен превратиться в \/ — клиенты такое читают, но
        // ссылка становится нечитаемой при отладке.
        $this->assertSame('/4eb9146850', $relabelled['path']);
    }

    public function test_vless_node_is_still_labelled_through_the_fragment(): void
    {
        config([
            'vpn.shared_home_uri' => '',
            'vpn.shared_cellular_uri' => 'vless://uuid@cdn.example:443?encryption=none#OldName',
        ]);

        $uris = SharedVpnAccess::namedNodeUris();

        $this->assertCount(1, $uris);
        $this->assertSame('vless://uuid@cdn.example:443?encryption=none#🇷🇺 Сотовая сеть', $uris[0]);
    }

    public function test_broken_vmess_link_falls_back_to_fragment_labelling(): void
    {
        config(['vpn.shared_home_uri' => 'vmess://not-base64-at-all!!', 'vpn.shared_cellular_uri' => '']);

        $uris = SharedVpnAccess::namedNodeUris();

        $this->assertCount(1, $uris);
        $this->assertStringEndsWith('#🇫🇮 Домашний интернет', $uris[0]);
    }

    public function test_subscription_body_is_base64_of_the_labelled_nodes(): void
    {
        config(['vpn.shared_home_uri' => self::VMESS, 'vpn.shared_cellular_uri' => '']);

        $decoded = base64_decode(SharedVpnAccess::subscriptionBody(), true);

        $this->assertSame(SharedVpnAccess::namedNodeUris(), explode("\n", $decoded));
    }
}
