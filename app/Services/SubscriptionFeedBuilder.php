<?php

namespace App\Services;

use App\Models\SaleKey;
use App\Models\TrialKey;
use App\Support\HappSubscriptionFormatter;

/**
 * Текст подписки и заголовки для Happ / клиентов (trial и платные ключи).
 */
class SubscriptionFeedBuilder
{
    public function __construct(
        protected TrialKeyService $trialKeyService,
        protected SaleKeyService $saleKeyService
    ) {}

    public function buildForTrial(TrialKey $trialKey): array
    {
        if (! $trialKey->isActive()) {
            return ['error' => 'Пробный период недоступен', 'code' => 403];
        }

        $this->trialKeyService->syncTrafficFromPanel($trialKey);
        $trialKey->refresh();

        $inbound = $this->trialKeyService->getInboundSettings($trialKey);
        if (! $inbound) {
            return ['error' => 'Ошибка получения настроек', 'code' => 500];
        }

        $serverIp = config('admin.test_panel.server_ip');
        $line = HappSubscriptionFormatter::vlessLineFromInbound(
            $inbound,
            $trialKey->uuid,
            $serverIp,
            '🇷🇺 AVA тестовый период'
        );

        $userInfo = HappSubscriptionFormatter::buildUserInfo(
            0,
            (int) $trialKey->used_bytes,
            (int) $trialKey->total_bytes,
            $trialKey->expires_at->timestamp
        );

        return [
            'body' => $line,
            'user_info' => $userInfo,
            'profile_title' => 'AVA тестовый период',
        ];
    }

    public function buildForSale(SaleKey $saleKey): array
    {
        $this->saleKeyService->syncTrafficFromPanel($saleKey);
        $saleKey->refresh();

        if ($saleKey->status !== 'active' || $saleKey->isExpired() || $saleKey->isTrafficExceeded()) {
            return ['error' => 'Подписка не активна или лимит исчерпан', 'code' => 403];
        }

        if (! $saleKey->subscription?->isActive()) {
            return ['error' => 'Подписка не активна', 'code' => 403];
        }

        if ($saleKey->is_admin_bundle) {
            return $this->buildAdminBundleBody($saleKey);
        }

        $panel = $this->saleKeyService->getSalePanelConfig($saleKey->panel_index);
        $inbound = $this->saleKeyService->getInboundForPanel($saleKey->panel_index, (int) $saleKey->inbound_id);
        if (! $inbound) {
            return ['error' => 'Ошибка получения настроек', 'code' => 500];
        }

        $lines = [];
        $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
            $inbound,
            $saleKey->uuid,
            $panel['server_ip'],
            '🇷🇺 AVA '.($saleKey->is_sponsor ? 'Спонсор NL' : 'VPN')
        );

        if ($saleKey->is_sponsor && $saleKey->secondary_uuid && $saleKey->secondary_panel_index !== null) {
            $p2 = $this->saleKeyService->getSalePanelConfig((int) $saleKey->secondary_panel_index);
            $inbound2 = $this->saleKeyService->getInboundForPanel(
                (int) $saleKey->secondary_panel_index,
                (int) $saleKey->secondary_inbound_id
            );
            if ($inbound2) {
                $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                    $inbound2,
                    (string) $saleKey->secondary_uuid,
                    $p2['server_ip'],
                    '🇪🇺 AVA Спонсор FR'
                );
            }
        }

        $body = implode("\n", $lines);

        $total = $saleKey->total_bytes > 0 ? (int) $saleKey->total_bytes : 0;
        $userInfo = HappSubscriptionFormatter::buildUserInfo(
            0,
            (int) $saleKey->used_bytes,
            $total,
            $saleKey->expires_at->timestamp
        );

        return [
            'body' => $body,
            'user_info' => $userInfo,
            'profile_title' => $saleKey->is_sponsor ? 'AVA Спонсор' : 'AVA VPN',
        ];
    }

    /**
     * @return array{body: string, user_info: string, profile_title: string}|array{error: string, code: int}
     */
    protected function buildAdminBundleBody(SaleKey $saleKey): array
    {
        $lines = [];

        if ($saleKey->admin_primary_is_test) {
            $tp = $this->saleKeyService->getTestPanelConfig();
            $inbound = $this->saleKeyService->getInboundForTestPanel((int) $saleKey->inbound_id);
            if (! $inbound) {
                return ['error' => 'Ошибка получения настроек (тест)', 'code' => 500];
            }
            $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                $inbound,
                $saleKey->uuid,
                $tp['server_ip'],
                '🧪 AVA Тест'
            );
        } else {
            $panel = $this->saleKeyService->getSalePanelConfig($saleKey->panel_index);
            $inbound = $this->saleKeyService->getInboundForPanel($saleKey->panel_index, (int) $saleKey->inbound_id);
            if (! $inbound) {
                return ['error' => 'Ошибка получения настроек', 'code' => 500];
            }
            $saleName = config('admin.sale_panels')[$saleKey->panel_index]['name'] ?? 'VPN';
            $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                $inbound,
                $saleKey->uuid,
                $panel['server_ip'],
                '🇷🇺 AVA '.$saleName
            );
        }

        foreach ($saleKey->bundle_endpoints ?? [] as $ep) {
            if (($ep['t'] ?? '') === 'test') {
                $tp = $this->saleKeyService->getTestPanelConfig();
                $inbound = $this->saleKeyService->getInboundForTestPanel((int) ($ep['inbound_id'] ?? 0));
                if (! $inbound) {
                    continue;
                }
                $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                    $inbound,
                    (string) ($ep['uuid'] ?? ''),
                    $tp['server_ip'],
                    '🧪 AVA Тест'
                );
            } else {
                $idx = (int) ($ep['i'] ?? 0);
                $panel = $this->saleKeyService->getSalePanelConfig($idx);
                $inbound = $this->saleKeyService->getInboundForPanel($idx, (int) ($ep['inbound_id'] ?? 0));
                if (! $inbound) {
                    continue;
                }
                $saleName = config('admin.sale_panels')[$idx]['name'] ?? 'VPN';
                $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                    $inbound,
                    (string) ($ep['uuid'] ?? ''),
                    $panel['server_ip'],
                    '🇷🇺 AVA '.$saleName
                );
            }
        }

        $body = implode("\n", $lines);
        $total = $saleKey->total_bytes > 0 ? (int) $saleKey->total_bytes : 0;
        $userInfo = HappSubscriptionFormatter::buildUserInfo(
            0,
            (int) $saleKey->used_bytes,
            $total,
            $saleKey->expires_at->timestamp
        );

        return [
            'body' => $body,
            'user_info' => $userInfo,
            'profile_title' => 'AVA Команда',
        ];
    }
}
