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
            'body' => $this->appendHappRoutingRules($line),
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
        $label1 = $this->happLineTitleForPanel((int) $saleKey->panel_index);
        $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
            $inbound,
            $saleKey->uuid,
            $panel['server_ip'],
            $label1
        );

        if ($saleKey->is_sponsor && $saleKey->secondary_uuid && $saleKey->secondary_panel_index !== null) {
            $p2 = $this->saleKeyService->getSalePanelConfig((int) $saleKey->secondary_panel_index);
            $inbound2 = $this->saleKeyService->getInboundForPanel(
                (int) $saleKey->secondary_panel_index,
                (int) $saleKey->secondary_inbound_id
            );
            if ($inbound2) {
                $label2 = $this->happLineTitleForPanel((int) $saleKey->secondary_panel_index);
                $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                    $inbound2,
                    (string) $saleKey->secondary_uuid,
                    $p2['server_ip'],
                    $label2
                );
            }
        }

        $body = $this->appendHappRoutingRules(implode("\n", $lines));

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
            'profile_title' => 'AVA VPN',
        ];
    }

    /**
     * Короткое имя сервера в подписке: «AVA · Связка 1 (NL)» из config admin.sale_panels.
     */
    protected function happLineTitleForPanel(int $panelIndex): string
    {
        $name = config('admin.sale_panels')[$panelIndex]['name'] ?? ('Сервер '.($panelIndex + 1));

        return 'AVA · '.$name;
    }

    /**
     * @return array{body: string, user_info: string, profile_title: string}|array{error: string, code: int}
     */
    protected function buildAdminBundleBody(SaleKey $saleKey): array
    {
        /**
         * Подписка «все серверы» — только продажные связки (config sale_panels).
         * Тестовая панель изолирована (только trial / отдельный URL); в старых записях в bundle_endpoints
         * мог остаться test — не включаем в тело подписки.
         */
        $lines = [];
        $seenPanelIndex = [];

        $appendSaleLine = function (int $panelIndex, string $uuid, int $inboundId) use (&$lines, &$seenPanelIndex): void {
            if (isset($seenPanelIndex[$panelIndex])) {
                return;
            }
            $seenPanelIndex[$panelIndex] = true;

            $panel = $this->saleKeyService->getSalePanelConfig($panelIndex);
            $inbound = $this->saleKeyService->getInboundForPanel($panelIndex, $inboundId);
            if (! $inbound) {
                return;
            }

            $lines[] = HappSubscriptionFormatter::vlessLineFromInbound(
                $inbound,
                $uuid,
                $panel['server_ip'],
                $this->happLineTitleForPanel($panelIndex)
            );
        };

        if (! $saleKey->admin_primary_is_test) {
            $appendSaleLine((int) $saleKey->panel_index, (string) $saleKey->uuid, (int) $saleKey->inbound_id);
        }

        foreach ($saleKey->bundle_endpoints ?? [] as $ep) {
            $type = $ep['t'] ?? 'sale';
            if ($type === 'test') {
                continue;
            }
            if ($type !== 'sale') {
                continue;
            }

            $appendSaleLine(
                (int) ($ep['i'] ?? 0),
                (string) ($ep['uuid'] ?? ''),
                (int) ($ep['inbound_id'] ?? 0)
            );
        }

        if ($lines === []) {
            return ['error' => 'Ошибка: в подписке нет ни одной продажной связки (проверьте ключ на панелях или перевыдайте подписку)', 'code' => 500];
        }

        $body = $this->appendHappRoutingRules(implode("\n", $lines));
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
            'profile_title' => 'AVA VPN',
        ];
    }

    /**
     * Дописывает правила маршрутизации в конец подписки (см. config('admin.happ_routing_rules')).
     */
    protected function appendHappRoutingRules(string $body): string
    {
        $rules = config('admin.happ_routing_rules', []);
        if (! is_array($rules) || $rules === []) {
            return $body;
        }

        $lines = array_values(array_filter(array_map('strval', $rules), fn (string $l) => $l !== ''));

        if ($lines === []) {
            return $body;
        }

        return rtrim($body, "\n")."\n\n".implode("\n", $lines);
    }
}
