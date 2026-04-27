<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\SaleKey;
use App\Models\TrialKey;
use App\Models\User;
use App\Services\TrialKeyService;
use App\Services\YooKassaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BotApiController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService,
        protected YooKassaService $yooKassaService,
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
     * GET /api/bot/subscriptions?telegram_id=...
     * Возвращает полный список подписок пользователя: все активные платные + пробный,
     * каждая со своей sub_link. Используется ботом для экрана «Мои подписки».
     */
    public function listSubscriptions(Request $request): JsonResponse
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
                'items' => [],
            ]);
        }

        return response()->json([
            'ok' => true,
            'user' => $this->serializeUser($user),
            'items' => $this->collectSubscriptionItems($user),
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

    /**
     * GET /api/bot/plans
     * Актуальный прайс-лист для бота (розничные тарифы, без спонсорских/админских бандлов).
     */
    public function listPlans(): JsonResponse
    {
        $plans = Plan::query()
            ->active()
            ->whereNotIn('slug', [
                \App\Services\SaleKeyService::SPONSOR_PLAN_SLUG,
                \App\Services\SaleKeyService::ADMIN_FRIENDS_PLAN_SLUG,
            ])
            ->ordered()
            ->get();

        return response()->json([
            'ok' => true,
            'plans' => $plans->map(fn (Plan $p) => [
                'slug' => $p->slug,
                'name' => $p->name,
                'devices' => (int) $p->devices,
                'days' => (int) $p->days,
                'price' => (int) $p->price,
                'discount' => (int) ($p->discount ?? 0),
                'is_popular' => (bool) $p->is_popular,
            ])->values(),
        ]);
    }

    /**
     * POST /api/bot/payment
     * Создаёт KeyOrder + YooKassa-платёж для бот-пользователя и возвращает ссылку на оплату.
     *
     * Тело:
     * - telegram_id (int)          — обязательно
     * - telegram_username (string) — опционально
     * - plan_slug (string)         — обязательно
     * - return_url (string)        — опционально (обычно t.me/<bot>?start=paid_<order_id>)
     */
    public function createPayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
            'plan_slug' => 'required|string|max:64',
            'return_url' => 'nullable|string|max:512',
            'source' => 'nullable|string|in:bot,web,unknown',
        ]);

        $plan = Plan::query()->where('slug', $data['plan_slug'])->active()->first();
        if (! $plan) {
            return response()->json([
                'ok' => false,
                'error' => 'plan_not_found',
                'message' => 'Тариф не найден или неактивен.',
            ], 404);
        }

        $user = $this->findOrCreateByTelegram(
            (int) $data['telegram_id'],
            $data['telegram_username'] ?? null
        );

        $order = KeyOrder::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => OrderStatus::Pending,
            'purchase_source' => $data['source'] ?? 'bot',
            'amount' => $plan->price,
            'note' => "Бот: оплата тарифа {$plan->name} ({$plan->period_label})",
        ]);

        $returnUrl = $data['return_url'] ?? $this->defaultBotReturnUrl($order->id);

        try {
            $payment = $this->yooKassaService->createPayment($user, $plan, $order, $returnUrl);
        } catch (\Throwable $e) {
            Log::error('Bot: createPayment failed', [
                'order_id' => $order->id,
                'plan_slug' => $plan->slug,
                'message' => $e->getMessage(),
            ]);
            $order->update(['status' => OrderStatus::Cancelled]);
            return response()->json([
                'ok' => false,
                'error' => 'payment_create_failed',
                'message' => 'Не удалось создать платёж.',
            ], 500);
        }

        if (! $payment) {
            $order->update(['status' => OrderStatus::Cancelled]);
            return response()->json([
                'ok' => false,
                'error' => 'payment_create_failed',
                'message' => 'YooKassa отклонил запрос платежа.',
            ], 502);
        }

        $order->update([
            'payment_id' => $payment['id'] ?? null,
            'payment_status' => $payment['status'] ?? null,
        ]);

        $confirmationUrl = $payment['confirmation']['confirmation_url'] ?? null;

        return response()->json([
            'ok' => true,
            'order_id' => (string) $order->id,
            'payment_id' => $order->payment_id,
            'amount' => (int) round(((float) $plan->price) * 100),
            'currency' => 'RUB',
            'status' => $this->mapYooKassaStatus($order->payment_status),
            'confirmation_url' => $confirmationUrl,
        ]);
    }

    /**
     * GET /api/bot/payment/status?order_id=...
     * Возвращает текущий статус платежа (pending|succeeded|canceled).
     * Если backend ещё не получил webhook — дополнительно дёргает YooKassa напрямую.
     */
    public function paymentStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => 'required',
        ]);

        $order = KeyOrder::query()->find((int) $data['order_id']);
        if (! $order) {
            return response()->json([
                'ok' => false,
                'error' => 'order_not_found',
            ], 404);
        }

        // Если платёж ещё не финальный — синхронно переспросим YooKassa.
        if ($order->payment_id && ! in_array($order->payment_status, ['succeeded', 'canceled'], true)) {
            try {
                $this->yooKassaService->checkPaymentStatus($order);
                $order->refresh();
            } catch (\Throwable $e) {
                Log::warning('Bot: paymentStatus YooKassa check failed', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'ok' => true,
            'order_id' => (string) $order->id,
            'amount' => (int) round(((float) ($order->amount ?? 0)) * 100),
            'status' => $this->mapYooKassaStatus($order->payment_status),
            'order_status' => $order->status?->value,
        ]);
    }

    protected function defaultBotReturnUrl(int $orderId): string
    {
        $botUsername = (string) (config('services.telegram_bot.username') ?: env('TELEGRAM_BOT_USERNAME', ''));
        $botUsername = ltrim($botUsername, '@');

        if ($botUsername !== '') {
            return 'https://t.me/'.$botUsername.'?start=paid_'.$orderId;
        }

        // Fallback: вернём на сайт, как раньше.
        return route('cabinet.subscription', ['order_id' => $orderId], true);
    }

    protected function mapYooKassaStatus(?string $paymentStatus): string
    {
        return match ($paymentStatus) {
            'succeeded' => 'succeeded',
            'canceled' => 'canceled',
            default => 'pending',
        };
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

    /**
     * Собрать список всех подписок пользователя для экрана «Мои подписки» в боте.
     * Возвращает массив элементов (каждый со своей sub_link). Если у пользователя
     * несколько активных платных подписок — все они вернутся списком.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function collectSubscriptionItems(User $user): array
    {
        $items = [];

        $activeSubscriptions = $user->activeSubscriptions()->with('plan')->get();

        $saleKeys = collect();
        if ($activeSubscriptions->isNotEmpty()) {
            $saleKeys = SaleKey::query()
                ->whereIn('subscription_id', $activeSubscriptions->pluck('id'))
                ->where('status', 'active')
                ->get()
                ->keyBy('subscription_id');
        }

        foreach ($activeSubscriptions as $sub) {
            /** @var SaleKey|null $key */
            $key = $saleKeys->get($sub->id);

            $planName = trim(
                ($sub->plan?->name ?? '')
                .($sub->plan?->days ? ' · '.$sub->plan->days.' дн.' : '')
            );

            $items[] = [
                'key' => 'sub_'.$sub->id,
                'subscription_id' => $sub->id,
                'status' => 'active',
                'is_trial' => false,
                'plan_slug' => $sub->plan?->slug,
                'plan_name' => $planName !== '' ? $planName : 'Подписка',
                'max_devices' => (int) $sub->max_devices,
                'expires_at' => $sub->expires_at?->toIso8601String(),
                'sub_link' => $key ? url('/sub/'.$key->sub_id) : null,
                'sub_id' => $key?->sub_id,
            ];
        }

        $trialKey = $user->trialKey;
        if ($trialKey && ! $trialKey->isExpired() && ! $trialKey->isTrafficExceeded()) {
            $items[] = [
                'key' => 'trial',
                'subscription_id' => null,
                'status' => 'trial',
                'is_trial' => true,
                'plan_slug' => 'trial-8h',
                'plan_name' => 'Пробный доступ',
                'max_devices' => 1,
                'expires_at' => $trialKey->expires_at?->toIso8601String(),
                'sub_link' => url('/sub/'.$trialKey->sub_id),
                'sub_id' => $trialKey->sub_id,
            ];
        }

        return $items;
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
