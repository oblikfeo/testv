<?php

namespace App\Console\Commands;

use App\Models\SaleKey;
use App\Services\SaleKeyService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Удаляет все спонсорские и админские (полный доступ) подписки с панелей и из БД.
 * После этого можно выдать ключи заново из админки.
 */
class PurgeSpecialSaleKeys extends Command
{
    protected $signature = 'ava:purge-special-keys {--force : Выполнить без интерактивного подтверждения}';

    protected $description = 'Удалить все sale_keys is_sponsor / is_admin_bundle (панели + БД)';

    public function handle(SaleKeyService $saleKeyService): int
    {
        $adminCount = SaleKey::query()->where('is_admin_bundle', true)->count();
        $sponsorCount = SaleKey::query()->where('is_sponsor', true)->count();

        if ($adminCount === 0 && $sponsorCount === 0) {
            $this->info('Нет спонсорских или админских ключей в БД.');

            return self::SUCCESS;
        }

        $this->warn("Будут удалены: админских — {$adminCount}, спонсорских — {$sponsorCount}.");

        if (! $this->option('force')) {
            if (! $this->confirm('Продолжить? Клиенты будут удалены с панелей 3x-ui.')) {
                return self::FAILURE;
            }
        }

        foreach (SaleKey::query()->where('is_admin_bundle', true)->get() as $key) {
            try {
                $saleKeyService->revokeAdminFriendsBundle($key);
                $this->line("Админская подписка id {$key->id} снята.");
            } catch (Throwable $e) {
                $this->error("Админ id {$key->id}: ".$e->getMessage());
            }
        }

        foreach (SaleKey::query()->where('is_sponsor', true)->get() as $key) {
            try {
                $saleKeyService->revokeSponsorBundle($key);
                $this->line("Спонсор id {$key->id} снят.");
            } catch (Throwable $e) {
                $this->error("Спонсор id {$key->id}: ".$e->getMessage());
            }
        }

        $this->info('Готово. Можно создавать подписки заново в админке.');

        return self::SUCCESS;
    }
}
