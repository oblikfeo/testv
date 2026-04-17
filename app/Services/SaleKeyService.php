<?php

namespace App\Services;

use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\SaleKey;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SaleKeyService
{
    public const SPONSOR_PLAN_SLUG = 'sponsor-bundle';

    public const MAX_SPONSOR_KEYS = 10;

    public function __construct(
        protected XuiApiService $xuiApi
    ) {}

    /**
     * @return array{url: string, username: string, password: string, server_ip: string}
     */
    public function getSalePanelConfig(int $panelIndex): array
    {
        $panels = config('admin.sale_panels', []);
        if (! isset($panels[$panelIndex])) {
            throw new \InvalidArgumentException('Неизвестная связка: '.$panelIndex);
        }

        return $panels[$panelIndex];
    }

    public function getActiveSalePanelIndex(): int
    {
        $v = Setting::get('active_sale_panel', '0');

        return (int) $v;
    }

    public function totalBytesFromPlan(Plan $plan): int
    {
        $gb = (int) ($plan->traffic_gb ?? 0);
        if ($gb <= 0) {
            return 0;
        }

        return $gb * 1024 * 1024 * 1024;
    }

    /**
     * После успешной оплаты: продление существующего ключа или создание нового.
     */
    public function syncAfterSuccessfulPayment(User $user, Subscription $subscription, Plan $plan, KeyOrder $order): void
    {
        if ($order->sale_key_id) {
            return;
        }

        $existing = SaleKey::query()
            ->where('subscription_id', $subscription->id)
            ->where('is_sponsor', false)
            ->first();

        if ($existing) {
            $this->extendSaleKey($existing, $subscription, $plan);

            $order->update(['sale_key_id' => $existing->id]);

            return;
        }

        $panelIndex = $this->getActiveSalePanelIndex();
        $saleKey = $this->createSaleKeyOnPanel($user, $subscription, $plan, $order, $panelIndex, false);

        $order->update(['sale_key_id' => $saleKey->id]);
    }

    public function extendSaleKey(SaleKey $saleKey, Subscription $subscription, Plan $plan): void
    {
        $totalBytes = $this->totalBytesFromPlan($plan);
        $expiryMs = (int) round($subscription->expires_at->timestamp * 1000);

        $saleKey->update([
            'expires_at' => $subscription->expires_at,
            'total_bytes' => $totalBytes > 0 ? $totalBytes : $saleKey->total_bytes,
            'status' => 'active',
        ]);

        $panel = $this->getSalePanelConfig($saleKey->panel_index);
        $clientPayload = $this->buildClientPayload(
            $subscription,
            $plan,
            $saleKey->email,
            $saleKey->uuid,
            $saleKey->sub_id,
            $expiryMs,
            $totalBytes
        );

        $result = $this->xuiApi->updateClient(
            $panel['url'],
            $panel['username'],
            $panel['password'],
            (int) $saleKey->inbound_id,
            $clientPayload
        );

        if (empty($result['success'])) {
            Log::error('SaleKey extend failed', [
                'sale_key_id' => $saleKey->id,
                'msg' => $result['msg'] ?? null,
            ]);
        }

        if ($saleKey->is_sponsor && $saleKey->secondary_uuid && $saleKey->secondary_panel_index !== null) {
            $panel2 = $this->getSalePanelConfig((int) $saleKey->secondary_panel_index);
            $result2 = $this->xuiApi->updateClient(
                $panel2['url'],
                $panel2['username'],
                $panel2['password'],
                (int) $saleKey->secondary_inbound_id,
                $this->buildClientPayload(
                    $subscription,
                    $plan,
                    (string) $saleKey->secondary_email,
                    (string) $saleKey->secondary_uuid,
                    (string) $saleKey->secondary_sub_id,
                    $expiryMs,
                    $totalBytes
                )
            );
            if (empty($result2['success'])) {
                Log::error('SaleKey sponsor secondary extend failed', [
                    'sale_key_id' => $saleKey->id,
                    'msg' => $result2['msg'] ?? null,
                ]);
            }
        }
    }

    public function createSaleKeyOnPanel(
        User $user,
        Subscription $subscription,
        Plan $plan,
        ?KeyOrder $order,
        int $panelIndex,
        bool $isSponsor,
        ?int $secondaryPanelIndex = null
    ): SaleKey {
        $panel = $this->getSalePanelConfig($panelIndex);
        $email = 'sale-'.$user->id.'-'.time().'-'.Str::random(4);
        $uuid = Str::uuid()->toString();
        $subId = Str::random(16);

        $inbounds = $this->xuiApi->getInbounds($panel['url'], $panel['username'], $panel['password']);
        if (empty($inbounds['obj'][0])) {
            throw new \RuntimeException('Нет inbound на связке '.$panelIndex);
        }

        $inboundId = (int) $inbounds['obj'][0]['id'];
        $expiryMs = (int) round($subscription->expires_at->timestamp * 1000);
        $totalBytes = $this->totalBytesFromPlan($plan);

        $clientPayload = $this->buildClientPayload(
            $subscription,
            $plan,
            $email,
            $uuid,
            $subId,
            $expiryMs,
            $totalBytes
        );

        $result = $this->xuiApi->addClient(
            $panel['url'],
            $panel['username'],
            $panel['password'],
            $inboundId,
            $clientPayload
        );

        if (empty($result['success'])) {
            throw new \RuntimeException($result['msg'] ?? 'Ошибка addClient на панели');
        }

        $secondaryUuid = null;
        $secondaryEmail = null;
        $secondarySubId = null;
        $secondaryInboundId = null;

        if ($isSponsor && $secondaryPanelIndex !== null && $secondaryPanelIndex !== $panelIndex) {
            $panel2 = $this->getSalePanelConfig($secondaryPanelIndex);
            $email2 = 'sale-'.$user->id.'-'.time().'-s-'.Str::random(4);
            $secondaryUuid = Str::uuid()->toString();
            $secondarySubId = Str::random(16);

            $inbounds2 = $this->xuiApi->getInbounds($panel2['url'], $panel2['username'], $panel2['password']);
            if (empty($inbounds2['obj'][0])) {
                throw new \RuntimeException('Нет inbound на второй связке');
            }

            $inboundId2 = (int) $inbounds2['obj'][0]['id'];
            $client2 = $this->buildClientPayload(
                $subscription,
                $plan,
                $email2,
                $secondaryUuid,
                $secondarySubId,
                $expiryMs,
                $totalBytes
            );

            $r2 = $this->xuiApi->addClient(
                $panel2['url'],
                $panel2['username'],
                $panel2['password'],
                $inboundId2,
                $client2
            );

            if (empty($r2['success'])) {
                throw new \RuntimeException($r2['msg'] ?? 'Ошибка addClient на второй панели');
            }

            $secondaryEmail = $email2;
            $secondaryInboundId = $inboundId2;
        }

        return SaleKey::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'key_order_id' => $order?->id,
            'panel_index' => $panelIndex,
            'uuid' => $uuid,
            'email' => $email,
            'sub_id' => $subId,
            'inbound_id' => $inboundId,
            'total_bytes' => $totalBytes,
            'used_bytes' => 0,
            'expires_at' => $subscription->expires_at,
            'activated_at' => now(),
            'is_sponsor' => $isSponsor,
            'secondary_panel_index' => $secondaryPanelIndex,
            'secondary_uuid' => $secondaryUuid,
            'secondary_email' => $secondaryEmail,
            'secondary_sub_id' => $secondarySubId,
            'secondary_inbound_id' => $secondaryInboundId,
            'status' => 'active',
        ]);
    }

    /**
     * Спонсорская подписка: две связки, один URL подписки (primary sub_id), в теле — два VLESS.
     */
    public function createSponsorBundle(User $user, int $days, int $trafficGb, int $maxDevices = 5): SaleKey
    {
        $count = SaleKey::query()->where('is_sponsor', true)->count();
        if ($count >= self::MAX_SPONSOR_KEYS) {
            throw new \RuntimeException('Достигнут лимит спонсорских подписок ('.self::MAX_SPONSOR_KEYS.')');
        }

        $plan = Plan::query()->where('slug', self::SPONSOR_PLAN_SLUG)->first();
        if (! $plan) {
            throw new \RuntimeException('Не найден тариф sponsor-bundle (запустите сидер)');
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'max_devices' => $maxDevices,
            'starts_at' => now(),
            'expires_at' => now()->addDays($days),
        ]);

        $planOverride = clone $plan;
        $planOverride->traffic_gb = $trafficGb;

        $primaryIndex = 0;
        $secondaryIndex = 1;
        if (count(config('admin.sale_panels', [])) < 2) {
            throw new \RuntimeException('Нужны две связки в config admin.sale_panels');
        }

        return $this->createSaleKeyOnPanel(
            $user,
            $subscription,
            $planOverride,
            null,
            $primaryIndex,
            true,
            $secondaryIndex
        );
    }

    protected function buildClientPayload(
        Subscription $subscription,
        Plan $plan,
        string $email,
        string $uuid,
        string $subId,
        int $expiryTimeMs,
        int $totalBytes
    ): array {
        $limitIp = max(1, (int) $plan->devices);

        return [
            'id' => $uuid,
            'email' => $email,
            'enable' => true,
            'expiryTime' => $expiryTimeMs,
            'totalGB' => $totalBytes,
            'limitIp' => $limitIp,
            'flow' => 'xtls-rprx-vision',
            'subId' => $subId,
            'tgId' => '',
            'reset' => 0,
        ];
    }

    public function syncTrafficFromPanel(SaleKey $saleKey): void
    {
        try {
            $panel = $this->getSalePanelConfig($saleKey->panel_index);
            $this->applyTrafficForEmail($panel, $saleKey->email, $saleKey);
        } catch (\Throwable $e) {
            Log::warning('SaleKey syncTraffic', ['id' => $saleKey->id, 'e' => $e->getMessage()]);
        }
    }

    protected function applyTrafficForEmail(array $panel, string $email, SaleKey $saleKey): void
    {
        $inbounds = $this->xuiApi->getInbounds($panel['url'], $panel['username'], $panel['password']);
        if (empty($inbounds['obj'])) {
            return;
        }

        foreach ($inbounds['obj'] as $inbound) {
            foreach ($inbound['clientStats'] ?? [] as $client) {
                if (($client['email'] ?? '') === $email) {
                    $saleKey->update([
                        'used_bytes' => (int) (($client['up'] ?? 0) + ($client['down'] ?? 0)),
                    ]);

                    return;
                }
            }
        }
    }

    public function getInboundForPanel(int $panelIndex, ?int $inboundIdFilter = null): ?array
    {
        $panel = $this->getSalePanelConfig($panelIndex);
        $inbounds = $this->xuiApi->getInbounds($panel['url'], $panel['username'], $panel['password']);
        if (empty($inbounds['obj'])) {
            return null;
        }

        foreach ($inbounds['obj'] as $inbound) {
            if ($inboundIdFilter === null || (int) $inbound['id'] === $inboundIdFilter) {
                return $inbound;
            }
        }

        return $inbounds['obj'][0] ?? null;
    }
}
