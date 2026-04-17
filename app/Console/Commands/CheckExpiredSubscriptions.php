<?php

namespace App\Console\Commands;

use App\Models\SaleKey;
use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
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
                    ->update(['status' => 'expired']);
                $count++;
            });

        $this->info("Обновлено подписок: {$count}");

        return self::SUCCESS;
    }
}
