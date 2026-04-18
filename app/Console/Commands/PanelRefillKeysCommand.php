<?php

namespace App\Console\Commands;

use App\Models\Pair;
use App\Services\KeyPoolService;
use Illuminate\Console\Command;

class PanelRefillKeysCommand extends Command
{
    protected $signature = 'panel:refill-keys
                            {pair? : ID связки в БД}
                            {--count= : Сколько ключей создать (по умолчанию batch_size связки)}';

    protected $description = 'Дозаполнить пул ключей через API панели (3x-ui) для указанной связки';

    public function handle(KeyPoolService $pool): int
    {
        $pairId = $this->argument('pair');
        if ($pairId === null) {
            $this->error('Укажите ID связки.');

            return self::FAILURE;
        }

        $pair = Pair::query()->find($pairId);
        if ($pair === null) {
            $this->error('Панель не найдена.');

            return self::FAILURE;
        }

        $count = $this->option('count');
        $count = $count !== null ? (int) $count : (int) $pair->batch_size;

        $this->info("Создаю {$count} ключ(ей) для связки «{$pair->name}» (id={$pair->id})…");

        try {
            $created = $pool->refillPair($pair, $count);
            $this->info("Готово: создано записей: {$created}.");
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
