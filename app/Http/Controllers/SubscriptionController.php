<?php

namespace App\Http\Controllers;

use App\Models\TrialKey;
use App\Services\TrialKeyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService
    ) {}

    public function show(string $subId, Request $request)
    {
        $trialKey = TrialKey::where('sub_id', $subId)->first();

        if (!$trialKey) {
            return response("Подписка не найдена", 404);
        }

        $this->trialKeyService->syncTrafficFromPanel($trialKey);
        $trialKey->refresh();

        $inbound = $this->trialKeyService->getInboundSettings($trialKey);

        if (!$inbound) {
            return response("Ошибка получения настроек", 500);
        }

        $config = $this->generateSubscriptionConfig($trialKey, $inbound);

        $userAgent = $request->header('User-Agent', '');
        
        if ($this->isHappClient($userAgent)) {
            return response($config)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('subscription-userinfo', $this->buildUserInfo($trialKey))
                ->header('profile-title', 'base64:' . base64_encode('AVA тестовый период'))
                ->header('profile-update-interval', '1')
                ->header('support-url', 'https://avavpn.ru');
        }

        return response($config)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('subscription-userinfo', $this->buildUserInfo($trialKey));
    }

    protected function generateSubscriptionConfig(TrialKey $trialKey, array $inbound): string
    {
        $streamSettings = json_decode($inbound['streamSettings'], true);
        $realitySettings = $streamSettings['realitySettings'] ?? [];

        $serverNames = $realitySettings['serverNames'] ?? ['www.cloudflare.com'];
        $publicKey = $realitySettings['settings']['publicKey'] ?? '';
        $shortIds = $realitySettings['shortIds'] ?? [''];

        $serverIp = config('admin.test_panel.server_ip');
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

        $vlessLink = sprintf(
            "vless://%s@%s:%d?%s#%s",
            $trialKey->uuid,
            $serverIp,
            $port,
            http_build_query($params),
            urlencode('🇷🇺 AVA тестовый период')
        );

        return $vlessLink;
    }

    protected function buildUserInfo(TrialKey $trialKey): string
    {
        $upload = 0;
        $download = $trialKey->used_bytes;
        $total = $trialKey->total_bytes;
        $expire = $trialKey->expires_at->timestamp;

        return sprintf(
            "upload=%d; download=%d; total=%d; expire=%d",
            $upload,
            $download,
            $total,
            $expire
        );
    }

    protected function isHappClient(string $userAgent): bool
    {
        $happClients = ['happ', 'hiddify', 'v2rayn', 'v2rayng', 'streisand', 'shadowrocket', 'quantumult', 'clash'];
        $userAgentLower = strtolower($userAgent);

        foreach ($happClients as $client) {
            if (str_contains($userAgentLower, $client)) {
                return true;
            }
        }

        return false;
    }
}
