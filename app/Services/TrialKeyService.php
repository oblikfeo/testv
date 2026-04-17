<?php

namespace App\Services;

use App\Models\TrialKey;
use App\Models\User;
use Illuminate\Support\Str;

class TrialKeyService
{
    protected XuiApiService $xuiApi;

    public function __construct(XuiApiService $xuiApi)
    {
        $this->xuiApi = $xuiApi;
    }

    public function createTrialKey(User $user): TrialKey
    {
        if (!$user->canUseTrial()) {
            throw new \Exception('Пользователь не может получить тестовый ключ');
        }

        $testPanel = config('admin.test_panel');
        $email = 'trial-' . $user->id . '-' . time();
        $uuid = Str::uuid()->toString();
        $subId = Str::random(16);
        
        $expiryTime = now()->addHours(8)->timestamp * 1000;
        $totalBytes = 10 * 1024 * 1024 * 1024; // 10 GB

        $inbounds = $this->xuiApi->getInbounds(
            $testPanel['url'],
            $testPanel['username'],
            $testPanel['password']
        );

        if (empty($inbounds['obj'])) {
            throw new \Exception('Не найдены inbound на панели');
        }

        $inboundId = $inbounds['obj'][0]['id'];

        $result = $this->xuiApi->addClient(
            $testPanel['url'],
            $testPanel['username'],
            $testPanel['password'],
            $inboundId,
            [
                'id' => $uuid,
                'email' => $email,
                'enable' => true,
                'expiryTime' => $expiryTime,
                'totalGB' => $totalBytes,
                'limitIp' => 1,
                'flow' => 'xtls-rprx-vision',
                'subId' => $subId,
                'tgId' => '',
                'reset' => 0,
            ]
        );

        if (!$result['success']) {
            throw new \Exception($result['msg'] ?? 'Ошибка создания ключа на панели');
        }

        $trialKey = TrialKey::create([
            'user_id' => $user->id,
            'email' => $email,
            'uuid' => $uuid,
            'sub_id' => $subId,
            'panel_url' => $testPanel['url'],
            'inbound_id' => $inboundId,
            'total_bytes' => $totalBytes,
            'used_bytes' => 0,
            'expires_at' => now()->addHours(8),
            'activated_at' => now(),
        ]);

        $user->update(['trial_used' => true]);

        return $trialKey;
    }

    public function syncTrafficFromPanel(TrialKey $trialKey): void
    {
        $testPanel = config('admin.test_panel');

        try {
            $inbounds = $this->xuiApi->getInbounds(
                $testPanel['url'],
                $testPanel['username'],
                $testPanel['password']
            );

            if (!empty($inbounds['obj'])) {
                foreach ($inbounds['obj'] as $inbound) {
                    if (!empty($inbound['clientStats'])) {
                        foreach ($inbound['clientStats'] as $client) {
                            if ($client['email'] === $trialKey->email) {
                                $trialKey->update([
                                    'used_bytes' => ($client['up'] ?? 0) + ($client['down'] ?? 0),
                                ]);
                                return;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail, will use cached data
        }
    }

    public function getInboundSettings(TrialKey $trialKey): ?array
    {
        $testPanel = config('admin.test_panel');

        try {
            $inbounds = $this->xuiApi->getInbounds(
                $testPanel['url'],
                $testPanel['username'],
                $testPanel['password']
            );

            if (!empty($inbounds['obj'])) {
                foreach ($inbounds['obj'] as $inbound) {
                    if ($inbound['id'] === $trialKey->inbound_id) {
                        return $inbound;
                    }
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
