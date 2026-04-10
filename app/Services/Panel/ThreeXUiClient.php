<?php

namespace App\Services\Panel;

use App\Contracts\PanelClient;
use App\DTO\CreatedClientResult;
use App\Exceptions\PanelApiException;
use App\Models\Pair;
use Carbon\CarbonInterface;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ThreeXUiClient implements PanelClient
{
    public function createSubscription(Pair $pair): CreatedClientResult
    {
        $base = rtrim($pair->panel_base_url, '/');
        $host = parse_url($base, PHP_URL_HOST) ?? 'localhost';

        $jar = CookieJar::fromArray([], $host);

        $login = $this->pending($jar)
            ->asForm()
            ->post($base.config('panels.login_path'), [
                'username' => $pair->panel_username,
                'password' => $pair->panel_password,
            ]);

        if (! $login->successful()) {
            throw new PanelApiException('Panel login failed: HTTP '.$login->status());
        }

        if ($pair->inbound_id === null) {
            throw new PanelApiException('Укажите inbound_id у связки (pair) для создания клиента в панели.');
        }

        $clientId = (string) Str::uuid();
        $subId = Str::lower(Str::random(16));
        $email = ($pair->remark_prefix ? $pair->remark_prefix.'-' : '').substr($clientId, 0, 8);

        $client = [
            'id' => $clientId,
            'alterId' => 0,
            'email' => $email,
            'limitIp' => 0,
            'totalGB' => 0,
            'expiryTime' => 0,
            'enable' => true,
            'tgId' => '',
            'subId' => $subId,
        ];

        $payload = [
            'id' => (int) $pair->inbound_id,
            'settings' => json_encode(['clients' => [$client]], JSON_UNESCAPED_SLASHES),
        ];

        $add = $this->pending($jar)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post($base.config('panels.add_client_path'), $payload);

        if (! $add->successful()) {
            throw new PanelApiException(
                'Panel addClient failed: HTTP '.$add->status().' — проверьте путь API и формат тела для вашей сборки 3x-ui.'
            );
        }

        $json = $add->json();
        $link = null;
        if (is_array($json)) {
            $link = $json['link'] ?? data_get($json, 'obj.link');
        }

        if (! is_string($link) || $link === '') {
            $link = 'sub:'.$subId;
        }

        return new CreatedClientResult(
            connectionUrl: $link,
            panelClientId: $clientId,
            raw: is_array($json) ? $json : ['body' => $add->body()],
        );
    }

    public function updateExpiry(Pair $pair, string $panelClientId, CarbonInterface $expiresAt): void
    {
        // Реализуйте под вашу версию 3x-ui (updateClient / reset traffic / expiry).
        \Illuminate\Support\Facades\Log::debug('panel.updateExpiry not implemented', [
            'pair_id' => $pair->id,
            'panel_client_id' => $panelClientId,
            'expires' => $expiresAt->toIso8601String(),
        ]);
    }

    public function getClientTraffic(Pair $pair, string $panelClientId): ?array
    {
        return null;
    }

    private function pending(CookieJar $jar): \Illuminate\Http\Client\PendingRequest
    {
        $req = Http::timeout(config('panels.timeout'))
            ->connectTimeout(config('panels.connect_timeout'))
            ->retry(config('panels.retry'), 500)
            ->withOptions(['cookies' => $jar]);

        if (! config('panels.verify_ssl')) {
            $req = $req->withoutVerifying();
        }

        return $req;
    }
}
