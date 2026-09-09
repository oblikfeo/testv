<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\KeyOrder;
use App\Models\Subscription;
use App\Services\YooKassaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileRefundedPaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile-refunds {--hours=168 : Look back window for paid orders}';

    protected $description = 'Проверяет оплаченные заказы в YooKassa и деактивирует подписки по подтверждённым возвратам';

    public function __construct(
        protected YooKassaService $yooKassaService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $checked = 0;
        $refunded = 0;

        KeyOrder::query()
            ->where('status', OrderStatus::Fulfilled)
            ->whereNotNull('payment_id')
            ->where('paid_at', '>=', now()->subHours($hours))
            ->orderByDesc('id')
            ->each(function (KeyOrder $order) use (&$checked, &$refunded): void {
                $checked++;
                if ($this->yooKassaService->syncRefundStatus($order)) {
                    $refunded++;
                }
            });

        $repaired = $this->revokeAccessOnRefundedOrders($hours);

        $this->info("Проверено заказов: {$checked}; отключено по возврату: {$refunded}; доотозвано доступов: {$repaired}");

        return self::SUCCESS;
    }

    /**
     * Подстраховка: заказ помечен возвратом, а подписка по нему всё ещё активна.
     * Так получалось, когда возврат обрабатывался, но подписку по заказу найти
     * не удавалось (id подписки в заказе не сохранялся). Такие заказы больше не
     * попадают в основной проход — он смотрит только на fulfilled, — поэтому
     * добиваем их отдельно, без обращений к API ЮKassa.
     */
    private function revokeAccessOnRefundedOrders(int $hours): int
    {
        $repaired = 0;

        KeyOrder::query()
            ->where('payment_status', 'refunded')
            ->where('updated_at', '>=', now()->subHours($hours))
            ->orderByDesc('id')
            ->each(function (KeyOrder $order) use (&$repaired): void {
                $subscriptionId = $this->yooKassaService->resolveOrderSubscriptionId($order);

                if (! $subscriptionId) {
                    Log::error('Refund repair: subscription for refunded order not found', [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ]);

                    return;
                }

                $affected = Subscription::query()
                    ->where('id', $subscriptionId)
                    ->where(function ($query): void {
                        $query->where('status', 'active')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->update([
                        'status' => 'expired',
                        'expires_at' => now(),
                    ]);

                if ($affected > 0) {
                    $repaired++;
                    Log::warning('Refund repair: revoked access left active after refund', [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'subscription_id' => $subscriptionId,
                    ]);
                }
            });

        return $repaired;
    }
}
