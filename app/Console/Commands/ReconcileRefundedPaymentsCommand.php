<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\KeyOrder;
use App\Services\YooKassaService;
use Illuminate\Console\Command;

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

        $this->info("Проверено заказов: {$checked}; отключено по возврату: {$refunded}");

        return self::SUCCESS;
    }
}
