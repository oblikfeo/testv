<?php

namespace App\Console\Commands;

use App\Models\SaleKey;
use App\Services\SaleKeyService;
use Illuminate\Console\Command;

class BackfillPanelDeactivationForExpiredKeysCommand extends Command
{
    protected $signature = 'sale-keys:backfill-panel-deactivation {--hours=720 : Look back window for expired keys}';

    protected $description = 'Повторно отзывает на панели розничные ключи, уже помеченные expired в БД';

    public function __construct(
        protected SaleKeyService $saleKeyService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $checked = 0;
        $deactivated = 0;

        SaleKey::query()
            ->where('status', 'expired')
            ->where('is_sponsor', false)
            ->where('is_admin_bundle', false)
            ->where('updated_at', '>=', now()->subHours($hours))
            ->orderByDesc('id')
            ->each(function (SaleKey $saleKey) use (&$checked, &$deactivated): void {
                $checked++;
                $this->saleKeyService->deactivateRetailSaleKey($saleKey);
                $deactivated++;
            });

        $this->info("Проверено expired-ключей: {$checked}; отправлено удалений на панель: {$deactivated}");

        return self::SUCCESS;
    }
}
