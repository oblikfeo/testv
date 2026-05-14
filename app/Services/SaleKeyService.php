<?php

namespace App\Services;

use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\SaleKey;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Работа с записями SaleKey в БД.
 *
 * Модель «общая подписка для всех»: фид VLESS+Hysteria — статический из config('admin.endpoints').
 * SaleKey хранит только метаданные (срок, soft-квоту, sub_id), без обращения к 3x-ui.
 * Поля panel_index/inbound_id/uuid/email сохраняем для обратной совместимости (миграции,
 * админка, отчёты) — заполняем sentinel-значениями.
 */
class SaleKeyService
{
    public const SPONSOR_PLAN_SLUG = 'sponsor-bundle';

    public const ADMIN_FRIENDS_PLAN_SLUG = 'admin-friends-bundle';

    public const MAX_SPONSOR_KEYS = 10;

    public const SHARED_UUID_FALLBACK = '00000000-0000-0000-0000-000000000000';

    public function totalBytesFromPlan(Plan $plan): int
    {
        $gb = (int) ($plan->traffic_gb ?? 0);
        if ($gb <= 0) {
            if (in_array($plan->slug, [self::SPONSOR_PLAN_SLUG, self::ADMIN_FRIENDS_PLAN_SLUG], true)) {
                return 0;
            }
            $fallbackGb = (int) config('admin.default_retail_traffic_gb', 0);
            if ($fallbackGb > 0) {
                return $fallbackGb * 1024 * 1024 * 1024;
            }

            return 0;
        }

        return $gb * 1024 * 1024 * 1024;
    }

    /**
     * После успешной оплаты: продление существующего ключа или создание нового.
     * В новой модели — только запись в БД, без обращений к 3x-ui.
     */
    public function syncAfterSuccessfulPayment(User $user, Subscription $subscription, Plan $plan, KeyOrder $order): void
    {
        if ($order->sale_key_id) {
            return;
        }

        if ($order->purchase_action === 'renew_subscription') {
            $existing = SaleKey::query()
                ->where('subscription_id', $subscription->id)
                ->where('is_sponsor', false)
                ->where('is_admin_bundle', false)
                ->first();

            if ($existing) {
                $this->extendSaleKey($existing, $subscription, $plan);
                $order->update(['sale_key_id' => $existing->id]);

                return;
            }
        }

        $saleKey = $this->createSaleKeyRecord($user, $subscription, $plan, $order, false);
        $order->update(['sale_key_id' => $saleKey->id]);
    }

    public function extendSaleKey(SaleKey $saleKey, Subscription $subscription, Plan $plan): void
    {
        $totalBytes = $this->totalBytesFromPlan($plan);

        $saleKey->update([
            'expires_at' => $subscription->expires_at,
            'total_bytes' => $totalBytes > 0 ? $totalBytes : $saleKey->total_bytes,
            'status' => 'active',
        ]);
    }

    /**
     * Спонсорская подписка: в старой модели — две связки, два UUID. В новой — одна запись SaleKey,
     * фид общий (5 endpoint'ов). Поле is_sponsor сохраняем для совместимости и админ-отчётов.
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

        return $this->createSaleKeyRecord($user, $subscription, $planOverride, null, true);
    }

    /**
     * Подписка «полный доступ»: одна запись SaleKey, общий фид (как у всех).
     * Сохраняем is_admin_bundle=true для отображения в админке.
     */
    public function createAdminFriendsBundle(User $user, int $days, int $trafficGb, int $maxDevices = 10): SaleKey
    {
        if (SaleKey::query()->where('is_admin_bundle', true)->exists()) {
            throw new \RuntimeException('Админская подписка уже выдана. Сначала отзовите её.');
        }

        $plan = Plan::query()->where('slug', self::ADMIN_FRIENDS_PLAN_SLUG)->firstOrFail();

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

        return $this->createSaleKeyRecord(
            $user,
            $subscription,
            $planOverride,
            null,
            false,
            isAdminBundle: true,
        );
    }

    /**
     * Снять спонсорскую подписку.
     */
    public function revokeSponsorBundle(SaleKey $saleKey): void
    {
        if (! $saleKey->is_sponsor) {
            throw new \InvalidArgumentException('Не спонсорская подписка');
        }

        $subscription = $saleKey->subscription;
        $saleKey->delete();
        $subscription?->delete();
    }

    public function revokeAdminFriendsBundle(SaleKey $saleKey): void
    {
        if (! $saleKey->is_admin_bundle) {
            throw new \InvalidArgumentException('Не админская подписка');
        }

        $subscription = $saleKey->subscription;
        $saleKey->delete();
        $subscription?->delete();
    }

    public function deactivateRetailSaleKey(SaleKey $saleKey): void
    {
        if ($saleKey->is_sponsor || $saleKey->is_admin_bundle) {
            return;
        }

        $saleKey->update([
            'status' => 'expired',
            'expires_at' => now(),
        ]);
    }

    /**
     * Совместимость со старым кодом: точный учёт трафика по пользователю невозможен,
     * пока UUID общий для всех. Метод оставлен no-op'ом.
     */
    public function syncTrafficFromPanel(SaleKey $saleKey): void
    {
        // no-op: общий UUID, агрегированный трафик в 3x-ui не делится на пользователей.
    }

    /**
     * Создаёт запись SaleKey с sentinel-полями panel_index/inbound_id/uuid/email,
     * чтобы существующие индексы / NOT NULL не падали.
     */
    protected function createSaleKeyRecord(
        User $user,
        Subscription $subscription,
        Plan $plan,
        ?KeyOrder $order,
        bool $isSponsor,
        bool $isAdminBundle = false,
    ): SaleKey {
        $sharedUuid = (string) config('admin.shared.vless_uuid', '');
        if ($sharedUuid === '') {
            $sharedUuid = self::SHARED_UUID_FALLBACK;
            Log::warning('SaleKeyService: SHARED_VLESS_UUID не задан — использован fallback UUID', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);
        }

        $totalBytes = $this->totalBytesFromPlan($plan);
        $subId = Str::random(16);
        $email = 'sale-'.$user->id.'-'.time().'-'.Str::random(4);

        return SaleKey::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'key_order_id' => $order?->id,
            'panel_index' => 0,
            'uuid' => $sharedUuid,
            'email' => $email,
            'sub_id' => $subId,
            'inbound_id' => 0,
            'total_bytes' => $totalBytes,
            'used_bytes' => 0,
            'expires_at' => $subscription->expires_at,
            'activated_at' => now(),
            'is_sponsor' => $isSponsor,
            'secondary_panel_index' => null,
            'secondary_uuid' => null,
            'secondary_email' => null,
            'secondary_sub_id' => null,
            'secondary_inbound_id' => null,
            'is_admin_bundle' => $isAdminBundle,
            'admin_primary_is_test' => false,
            'bundle_endpoints' => [],
            'status' => 'active',
        ]);
    }

    /* ---------------------------------------------------------------------
     * Заглушки старых методов, оставлены для обратной совместимости
     * (вызовы из админки и админ-команд проходят без ошибок).
     * --------------------------------------------------------------------- */

    public function getActiveSalePanelIndex(): int
    {
        return 0;
    }

    /**
     * @return array{url: string, username: string, password: string, server_ip: string}
     */
    public function getSalePanelConfig(int $panelIndex): array
    {
        $panels = config('admin.sale_panels', []);
        if (isset($panels[$panelIndex])) {
            return $panels[$panelIndex];
        }

        return [
            'url' => '',
            'username' => '',
            'password' => '',
            'server_ip' => '',
        ];
    }

    /**
     * @return array{url: string, username: string, password: string, server_ip: string}
     */
    public function getTestPanelConfig(): array
    {
        $t = config('admin.test_panel', []);

        return [
            'url' => (string) ($t['url'] ?? ''),
            'username' => (string) ($t['username'] ?? ''),
            'password' => (string) ($t['password'] ?? ''),
            'server_ip' => (string) ($t['server_ip'] ?? ''),
        ];
    }

    public function getInboundForPanel(int $panelIndex, ?int $inboundIdFilter = null): ?array
    {
        return null;
    }

    public function getInboundForTestPanel(?int $inboundIdFilter = null): ?array
    {
        return null;
    }
}
