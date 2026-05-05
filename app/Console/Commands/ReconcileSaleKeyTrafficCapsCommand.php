<?php

namespace App\Console\Commands;

use App\Models\SaleKey;
use App\Services\SaleKeyService;
use Illuminate\Console\Command;

class ReconcileSaleKeyTrafficCapsCommand extends Command
{
    protected $signature = 'sale-keys:reconcile-traffic-caps {--dry-run : Only show mismatches}';

    protected $description = 'Сверяет лимит трафика активных ключей с тарифом и обновляет ключи в панели';

    public function __construct(
        protected SaleKeyService $saleKeyService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $checked = 0;
        $mismatched = 0;
        $updated = 0;

        SaleKey::query()
            ->where('status', 'active')
            ->where('is_sponsor', false)
            ->where('is_admin_bundle', false)
            ->with(['subscription.plan'])
            ->orderByDesc('id')
            ->each(function (SaleKey $saleKey) use ($dryRun, &$checked, &$mismatched, &$updated): void {
                $checked++;

                $subscription = $saleKey->subscription;
                $plan = $subscription?->plan;

                if (! $subscription || ! $plan || $subscription->status !== 'active') {
                    return;
                }

                $expectedBytes = $this->saleKeyService->totalBytesFromPlan($plan);
                if ($expectedBytes <= 0 || (int) $saleKey->total_bytes === $expectedBytes) {
                    return;
                }

                $mismatched++;
                $this->line("sale_key #{$saleKey->id}: {$saleKey->total_bytes} -> {$expectedBytes} bytes ({$plan->slug})");

                if ($dryRun) {
                    return;
                }

                $this->saleKeyService->extendSaleKey($saleKey, $subscription, $plan);
                $updated++;
            });

        $this->info("Проверено: {$checked}; несоответствий: {$mismatched}; обновлено: {$updated}");

        return self::SUCCESS;
    }
}
