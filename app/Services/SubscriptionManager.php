<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Единая точка управления подписками. Используется вебхуком оплаты (YooKassaService),
 * ботом и админкой — чтобы логика создания/продления жила в одном месте.
 */
class SubscriptionManager
{
    /**
     * Создать новую подписку по тарифу.
     */
    public function createForPlan(User $user, Plan $plan, string $source = 'unknown'): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'purchase_source' => $source,
            'max_devices' => $plan->devices,
            'starts_at' => now(),
            'expires_at' => now()->addDays($plan->days),
        ]);
    }

    /**
     * Продлить существующую подписку тарифом: срок наращивается от текущей даты окончания
     * (если она в будущем) либо от «сейчас». Тариф/устройства обновляются на выбранный план.
     */
    public function extendWithPlan(Subscription $subscription, Plan $plan, string $source = 'unknown'): Subscription
    {
        $base = $this->baseDate($subscription);

        $subscription->update([
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => $base->addDays($plan->days),
            'max_devices' => $plan->devices,
            'purchase_source' => $source,
        ]);

        return $subscription->fresh();
    }

    /**
     * Продлить подписку на произвольное число дней (без смены тарифа). Для ручных действий в админке.
     */
    public function extendByDays(Subscription $subscription, int $days): Subscription
    {
        $base = $this->baseDate($subscription);

        $subscription->update([
            'status' => 'active',
            'expires_at' => $base->addDays(max(1, $days)),
        ]);

        return $subscription->fresh();
    }

    /**
     * Деактивировать подписку немедленно.
     */
    public function expire(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'expired',
            'expires_at' => now(),
        ]);

        return $subscription->fresh();
    }

    private function baseDate(Subscription $subscription): Carbon
    {
        return $subscription->expires_at && $subscription->expires_at->isFuture()
            ? $subscription->expires_at->copy()
            : now();
    }
}
