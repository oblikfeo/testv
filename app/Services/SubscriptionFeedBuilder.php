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
        $label = HappSubscriptionFormatter::happNodeLabel(
            (string) (config('admin.test_panel.happ_label') ?? '🇷🇺 Тест')
        );

        [$line, $err] = HappSubscriptionFormatter::vlessLineFromInboundOrError(
            $inbound,
            $trialKey->uuid,
            $serverIp,
            $label
        );
        if ($err !== null) {
            return ['error' => $err, 'code' => 500];
        }

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
        $label1 = $this->happLabelForPanel((int) $saleKey->panel_index);
        [$line1, $err1] = HappSubscriptionFormatter::vlessLineFromInboundOrError(
            $inbound,
            $saleKey->uuid,
            $panel['server_ip'],
            $label1
        );
        if ($err1 !== null) {
            return ['error' => $err1, 'code' => 500];
        }
        $lines[] = $line1;

        if ($saleKey->is_sponsor && $saleKey->secondary_uuid && $saleKey->secondary_panel_index !== null) {
            $p2 = $this->saleKeyService->getSalePanelConfig((int) $saleKey->secondary_panel_index);
            $inbound2 = $this->saleKeyService->getInboundForPanel(
                (int) $saleKey->secondary_panel_index,
                (int) $saleKey->secondary_inbound_id
            );
            if (! $inbound2) {
                return [
                    'error' => 'Нет inbound id='.(int) $saleKey->secondary_inbound_id.' на панели '.(int) $saleKey->secondary_panel_index.' — обновите secondary_inbound_id в БД под панель 3x-ui.',
                    'code' => 500,
                ];
            }
            $label2 = $this->happLabelForPanel((int) $saleKey->secondary_panel_index);
            [$line2, $err2] = HappSubscriptionFormatter::vlessLineFromInboundOrError(
                $inbound2,
                (string) $saleKey->secondary_uuid,
                $p2['server_ip'],
                $label2
            );
            if ($err2 !== null) {
                return ['error' => $err2, 'code' => 500];
            }
            $lines[] = $line2;
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
            'profile_title' => 'AVA VPN',
        ];
    }

    /**
     * Подпись узла в Happ: AVA + флаг + страна (admin.happ_brand + sale_panels.*.happ_label).
     */
    protected function happLabelForPanel(int $panelIndex): string
    {
        $panels = config('admin.sale_panels', []);
        $base = (string) ($panels[$panelIndex]['happ_label'] ?? ('🌐 '.($panelIndex + 1)));

        return HappSubscriptionFormatter::happNodeLabel($base);
    }

    /**
     * @return array{body: string, user_info: string, profile_title: string}|array{error: string, code: int}
     */
    protected function buildAdminBundleBody(SaleKey $saleKey): array
    {
        $lines = [];
        $seenPanelIndex = [];

        $appendSaleLine = function (int $panelIndex, string $uuid, int $inboundId) use (&$lines, &$seenPanelIndex): ?string {
            if (isset($seenPanelIndex[$panelIndex])) {
                return null;
            }
            $seenPanelIndex[$panelIndex] = true;

            $panel = $this->saleKeyService->getSalePanelConfig($panelIndex);
            $inbound = $this->saleKeyService->getInboundForPanel($panelIndex, $inboundId);
            if (! $inbound) {
                return 'Нет inbound на панели '.$panelIndex;
            }

            $label = $this->happLabelForPanel($panelIndex);
            [$line, $err] = HappSubscriptionFormatter::vlessLineFromInboundOrError(
                $inbound,
                $uuid,
                $panel['server_ip'],
                $label
            );
            if ($err !== null) {
                return $err;
            }
            $lines[] = $line;

            return null;
        };

        if (! $saleKey->admin_primary_is_test) {
            $e = $appendSaleLine((int) $saleKey->panel_index, (string) $saleKey->uuid, (int) $saleKey->inbound_id);
            if ($e !== null) {
                return ['error' => $e, 'code' => 500];
            }
        }

        foreach ($saleKey->bundle_endpoints ?? [] as $ep) {
            $type = $ep['t'] ?? 'sale';
            if ($type === 'test') {
                continue;
            }
            if ($type !== 'sale') {
                continue;
            }

            $e = $appendSaleLine(
                (int) ($ep['i'] ?? 0),
                (string) ($ep['uuid'] ?? ''),
                (int) ($ep['inbound_id'] ?? 0)
            );
            if ($e !== null) {
                return ['error' => $e, 'code' => 500];
            }
        }

        if ($lines === []) {
            return ['error' => 'Ошибка: в подписке нет ни одного сервера продаж (проверьте ключи на панелях или перевыдайте подписку)', 'code' => 500];
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
            'profile_title' => 'AVA VPN',
        ];
    }
}
