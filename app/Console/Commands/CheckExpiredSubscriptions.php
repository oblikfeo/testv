<?php

namespace App\Console\Commands;

use App\Models\SaleKey;
use App\Models\Subscription;
use App\Services\SaleKeyService;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    public function __construct(
        protected SaleKeyService $saleKeyService
    ) {
        parent::__construct();
    }

    protected $signature = 'subscriptions:check-expired';

    protected $description = 'Помечает истёкшие подписки и связанные sale-ключи';

    public function handle(): int
    {
        $count = 0;

        Subscription::query()
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->each(function (Subscription $sub) use (&$count): void {
                $sub->update(['status' => 'expired']);

                SaleKey::query()
                    ->where('subscription_id', $sub->id)
                    ->get()
                    ->each(function (SaleKey $saleKey): void {
                        if (! $saleKey->is_sponsor && ! $saleKey->is_admin_bundle) {
                            $this->saleKeyService->deactivateRetailSaleKey($saleKey);

                            return;
                        }

                        $saleKey->update(['status' => 'expired']);
                    });
                $count++;
            });

        $this->info("Обновлено подписок: {$count}");

        return self::SUCCESS;
    }
}
