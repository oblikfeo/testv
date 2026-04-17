<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class XuiApiService
{
    protected array $sessions = [];

    public function login(string $baseUrl, string $username, string $password): array
    {
        $response = Http::asForm()
            ->withOptions(['verify' => false])
            ->post("{$baseUrl}/login", [
                'username' => $username,
                'password' => $password,
            ]);

        $cookies = $response->cookies();
        $this->sessions[$baseUrl] = $cookies;

        return $response->json() ?? ['success' => false, 'msg' => 'Failed to parse response'];
    }

    protected function getSession(string $baseUrl, string $username, string $password)
    {
        if (!isset($this->sessions[$baseUrl])) {
            $this->login($baseUrl, $username, $password);
        }
        return $this->sessions[$baseUrl];
    }

    public function getInbounds(string $baseUrl, string $username, string $password): array
    {
        $cookies = $this->getSession($baseUrl, $username, $password);

        $response = Http::withOptions(['verify' => false, 'cookies' => $cookies])
            ->get("{$baseUrl}/panel/api/inbounds/list");

        return $response->json() ?? ['success' => false, 'msg' => 'Failed to parse response'];
    }

    public function addClient(string $baseUrl, string $username, string $password, int $inboundId, array $clientData): array
    {
        $cookies = $this->getSession($baseUrl, $username, $password);

        $response = Http::withOptions(['verify' => false, 'cookies' => $cookies])
            ->asJson()
            ->post("{$baseUrl}/panel/api/inbounds/addClient", [
                'id' => $inboundId,
                'settings' => json_encode(['clients' => [$clientData]]),
            ]);

        return $response->json() ?? ['success' => false, 'msg' => 'Failed to parse response'];
    }

    public function deleteClient(string $baseUrl, string $username, string $password, int $inboundId, string $uuid): array
    {
        $cookies = $this->getSession($baseUrl, $username, $password);

        $response = Http::withOptions(['verify' => false, 'cookies' => $cookies])
            ->post("{$baseUrl}/panel/api/inbounds/{$inboundId}/delClient/{$uuid}");

        return $response->json() ?? ['success' => false, 'msg' => 'Failed to parse response'];
    }

    public function resetClientTraffic(string $baseUrl, string $username, string $password, int $inboundId, string $email): array
    {
        $cookies = $this->getSession($baseUrl, $username, $password);

        $response = Http::withOptions(['verify' => false, 'cookies' => $cookies])
            ->post("{$baseUrl}/panel/api/inbounds/{$inboundId}/resetClientTraffic/{$email}");

        return $response->json() ?? ['success' => false, 'msg' => 'Failed to parse response'];
    }
}
