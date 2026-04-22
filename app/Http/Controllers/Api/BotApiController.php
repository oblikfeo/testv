<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleKey;
use App\Models\TrialKey;
use App\Models\User;
use App\Services\TrialKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BotApiController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService,
    ) {}

    /**
     * POST /api/bot/user
     * Найти или создать User по telegram_id. Используется при первом /start.
     */
    public function ensureUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
        ]);

        $user = $this->findOrCreateByTelegram(
            (int) $data['telegram_id'],
            $data['telegram_username'] ?? null
        );

        return response()->json([
            'ok' => true,
            'user' => $this->serializeUser($user),
        ]);
    }

    /**
     * GET /api/bot/subscription?telegram_id=...
     * Возвращает актуальный статус: none | trial | active | expired.
     */
    public function getSubscription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
        ]);

        $user = User::query()
            ->where('telegram_id', (int) $data['telegram_id'])
            ->first();

        if (! $user) {
            return response()->json([
                'ok' => true,
                'user' => null,
                'subscription' => $this->emptySubscription(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'user' => $this->serializeUser($user),
            'subscription' => $this->resolveSubscription($user),
        ]);
    }

    /**
     * POST /api/bot/trial
     * Выпустить пробный ключ на 8 часов. Идемпотентно возвращает уже существующий ключ.
     */
    public function issueTrial(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
        ]);

        $user = $this->findOrCreateByTelegram(
            (int) $data['telegram_id'],
            $data['telegram_username'] ?? null
        );

        if ($user->trial_used) {
            return response()->json([
                'ok' => false,
                'error' => 'trial_already_used',
                'message' => 'Пробный ключ уже был выдан этому пользователю.',
                'user' => $this->serializeUser($user),
                'subscription' => $this->resolveSubscription($user),
            ], 409);
        }

        try {
            $trialKey = $this->trialKeyService->createTrialKey($user);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'trial_create_failed',
                'message' => $e->getMessage(),
            ], 500);
        }

        $user->refresh();

        return response()->json([
            'ok' => true,
            'user' => $this->serializeUser($user),
            'subscription' => $this->resolveSubscription($user, $trialKey),
        ]);
    }

    // ---------- helpers ----------

    protected function findOrCreateByTelegram(int $tgId, ?string $tgUsername): User
    {
        return DB::transaction(function () use ($tgId, $tgUsername): User {
            $user = User::query()->where('telegram_id', $tgId)->lockForUpdate()->first();

            if ($user) {
                if ($tgUsername && $user->telegram_username !== $tgUsername) {
                    $user->telegram_username = $tgUsername;
                    $user->save();
                }
                return $user;
            }

            $placeholderEmail = 'tg-'.$tgId.'@bot.avavpn.ru';
            $name = $tgUsername ? '@'.$tgUsername : ('TG '.$tgId);

            $user = User::create([
                'name' => $name,
                'email' => $placeholderEmail,
                // Случайный «непригодный» пароль: бот-пользователь не может войти по email.
                'password' => \Illuminate\Support\Str::random(40),
                'telegram_id' => $tgId,
                'telegram_username' => $tgUsername,
            ]);

            return $user;
        });
    }

    protected function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'telegram_username' => $user->telegram_username,
            'email' => $user->isBotOnly() ? null : $user->email,
            'trial_used' => (bool) $user->trial_used,
        ];
    }

    /**
     * @param  TrialKey|null  $trialKey  форсировать использование конкретного ключа (сразу после выдачи)
     */
    protected function resolveSubscription(User $user, ?TrialKey $trialKey = null): array
    {
        $activeSubscription = $user->activeSubscriptions()->with('plan')->first();
        if ($activeSubscription) {
            $saleKey = SaleKey::query()
                ->where('subscription_id', $activeSubscription->id)
                ->where('status', 'active')
                ->first();

            return [
                'status' => 'active',
                'plan_slug' => $activeSubscription->plan?->slug,
                'plan_name' => $activeSubscription->plan?->name,
                'max_devices' => $activeSubscription->max_devices,
                'expires_at' => $activeSubscription->expires_at?->toIso8601String(),
                'sub_link' => $saleKey ? url('/sub/'.$saleKey->sub_id) : null,
                'sub_id' => $saleKey?->sub_id,
            ];
        }

        $trialKey = $trialKey ?? $user->trialKey;
        if ($trialKey) {
            $isActive = ! $trialKey->isExpired() && ! $trialKey->isTrafficExceeded();

            return [
                'status' => $isActive ? 'trial' : 'expired',
                'plan_slug' => 'trial-8h',
                'plan_name' => 'Пробный доступ',
                'max_devices' => 1,
                'expires_at' => $trialKey->expires_at?->toIso8601String(),
                'sub_link' => url('/sub/'.$trialKey->sub_id),
                'sub_id' => $trialKey->sub_id,
            ];
        }

        return $this->emptySubscription();
    }

    protected function emptySubscription(): array
    {
        return [
            'status' => 'none',
            'plan_slug' => null,
            'plan_name' => null,
            'max_devices' => null,
            'expires_at' => null,
            'sub_link' => null,
            'sub_id' => null,
        ];
    }
}
