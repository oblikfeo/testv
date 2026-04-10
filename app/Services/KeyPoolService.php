<?php

namespace App\Services;

use App\Contracts\PanelClient;
use App\Enums\OrderStatus;
use App\Enums\SubscriptionKeyStatus;
use App\Models\KeyOrder;
use App\Models\Pair;
use App\Models\SubscriptionKey;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KeyPoolService
{
    public function __construct(
        protected PanelClient $panelClient
    ) {}

    /**
     * Создать в БД ключи, вызвав панель (по count раз).
     */
    public function refillPair(Pair $pair, ?int $count = null): int
    {
        $count ??= max(1, (int) $pair->batch_size);
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            $result = $this->panelClient->createSubscription($pair);

            SubscriptionKey::query()->create([
                'pair_id' => $pair->id,
                'status' => SubscriptionKeyStatus::Available->value,
                'connection_url' => $result->connectionUrl,
                'panel_client_id' => $result->panelClientId,
                'panel_raw' => $result->raw,
                'created_in_panel_at' => now(),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Если доступных ключей меньше порога — дозаполнить до batch_size.
     */
    public function ensurePoolForPair(Pair $pair): void
    {
        if (! $pair->is_active) {
            return;
        }

        $available = $pair->availableKeys()->count();
        if ($available >= $pair->refill_threshold) {
            return;
        }

        $need = max(0, (int) $pair->batch_size - $available);
        if ($need === 0) {
            return;
        }

        try {
            $this->refillPair($pair, $need);
        } catch (\Throwable $e) {
            Log::error('KeyPool ensure failed', [
                'pair_id' => $pair->id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Выдать один ключ пользователю (с дозаполнением пула при необходимости).
     */
    public function issueKeyToUser(User $user): SubscriptionKey
    {
        $pairs = Pair::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($pairs as $pair) {
            try {
                $this->ensurePoolForPair($pair);
            } catch (\Throwable $e) {
                Log::warning('KeyPool ensure skipped for pair', [
                    'pair_id' => $pair->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return DB::transaction(function () use ($user) {
            $pairs = Pair::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($pairs as $pair) {
                $key = SubscriptionKey::query()
                    ->where('pair_id', $pair->id)
                    ->where('status', SubscriptionKeyStatus::Available->value)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if ($key !== null) {
                    $key->update([
                        'status' => SubscriptionKeyStatus::Issued->value,
                        'user_id' => $user->id,
                        'issued_at' => now(),
                    ]);

                    return $key->fresh();
                }
            }

            throw new \RuntimeException('Нет доступных связок с ключами. Добавьте связку или выполните panel:refill-keys.');
        });
    }

    /**
     * Создать заказ и сразу выдать ключ (MVP без платёжного шлюза).
     */
    public function fulfillOrderForUser(User $user, ?string $note = null): KeyOrder
    {
        return DB::transaction(function () use ($user, $note) {
            $key = $this->issueKeyToUser($user);

            return KeyOrder::query()->create([
                'user_id' => $user->id,
                'status' => OrderStatus::Fulfilled,
                'subscription_key_id' => $key->id,
                'note' => $note,
            ]);
        });
    }

    public function markActivatedIfPending(SubscriptionKey $key): void
    {
        if ($key->status !== SubscriptionKeyStatus::Issued->value) {
            return;
        }

        $key->update([
            'status' => SubscriptionKeyStatus::Activated->value,
            'activated_at' => now(),
        ]);
    }
}
