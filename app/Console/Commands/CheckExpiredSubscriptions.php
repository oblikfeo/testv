<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';

    protected $description = 'Помечает истёкшие подписки';

    public function handle(): int
    {
        $count = Subscription::query()
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Обновлено подписок: {$count}");

        return self::SUCCESS;
    }
}
