<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\TrialFeedback;
use App\Models\TrialFeedbackRequest;
use App\Models\TrialKey;
use App\Models\User;
use App\Services\TrialKeyService;
use App\Services\YooKassaService;
use App\Support\SharedVpnAccess;
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
            'telegram_first_name' => 'nullable|string|max:64',
            'telegram_last_name' => 'nullable|string|max:64',
        ]);

        $user = $this->findOrCreateByTelegram(
            (int) $data['telegram_id'],
            $data['telegram_username'] ?? null,
            $data['telegram_first_name'] ?? null,
            $data['telegram_last_name'] ?? null,
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
     * Выпустить пробный ключ (срок из config admin.trial). Идемпотентно возвращает уже существующий ключ.
     */
    public function issueTrial(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
            'telegram_first_name' => 'nullable|string|max:64',
            'telegram_last_name' => 'nullable|string|max:64',
        ]);

        $user = $this->findOrCreateByTelegram(
            (int) $data['telegram_id'],
            $data['telegram_username'] ?? null,
            $data['telegram_first_name'] ?? null,
            $data['telegram_last_name'] ?? null,
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
            ->whereNotIn('slug', ['sponsor-bundle', 'admin-friends-bundle'])
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
            'telegram_first_name' => 'nullable|string|max:64',
            'telegram_last_name' => 'nullable|string|max:64',
            'plan_slug' => 'required|string|max:64',
            'return_url' => 'nullable|string|max:512',
            'source' => 'nullable|string|in:bot,web,unknown',
            'purchase_action' => 'required|string|in:new_purchase,renew_subscription',
            'target_subscription_id' => 'nullable|integer|exists:subscriptions,id',
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
            $data['telegram_username'] ?? null,
            $data['telegram_first_name'] ?? null,
            $data['telegram_last_name'] ?? null,
        );

        $purchaseAction = $data['purchase_action'];
        $targetSubscriptionId = null;
        if ($purchaseAction === 'renew_subscription') {
            $targetSubscriptionId = (int) ($data['target_subscription_id'] ?? 0);
            if ($targetSubscriptionId <= 0) {
                return response()->json([
                    'ok' => false,
                    'error' => 'target_subscription_required',
                    'message' => 'Для продления нужно выбрать подписку.',
                ], 422);
            }

            $targetSubscription = $user->subscriptions()
                ->where('id', $targetSubscriptionId)
                ->first();
            if (! $targetSubscription) {
                return response()->json([
                    'ok' => false,
                    'error' => 'target_subscription_not_found',
                    'message' => 'Подписка для продления не найдена.',
                ], 404);
            }
        }

        $order = KeyOrder::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => OrderStatus::Pending,
            'purchase_source' => $data['source'] ?? 'bot',
            'purchase_action' => $purchaseAction,
            'target_subscription_id' => $targetSubscriptionId,
            'amount' => $plan->price,
            'note' => $purchaseAction === 'renew_subscription'
                ? "Бот: продление подписки #{$targetSubscriptionId} ({$plan->name}, {$plan->period_label})"
                : "Бот: покупка новой подписки {$plan->name} ({$plan->period_label})",
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

    /**
     * POST /api/bot/trial-feedback
     * Сохраняет отзыв после завершения пробного доступа.
     */
    public function submitTrialFeedback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:64',
            'message' => 'required|string|min:3|max:4000',
            'trigger' => 'nullable|string|max:32',
        ]);

        $user = User::query()->where('telegram_id', (int) $data['telegram_id'])->first();

        TrialFeedback::create([
            'user_id' => $user?->id,
            'telegram_id' => (int) $data['telegram_id'],
            'telegram_username' => $data['telegram_username'] ?? $user?->telegram_username,
            'trigger' => $data['trigger'] ?? 'trial_expired',
            'message' => trim((string) $data['message']),
        ]);

        if ($user) {
            TrialFeedbackRequest::query()
                ->where('user_id', $user->id)
                ->whereNull('submitted_at')
                ->update(['submitted_at' => now()]);
        }

        return response()->json(['ok' => true]);
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

    /**
     * Найти пользователя по telegram_id или создать нового. При каждом вызове из бота
     * подтягиваем актуальные username/first_name/last_name (юзер мог установить @username
     * после первой регистрации, или поменять имя). Поле `users.name` — отображаемое
     * имя в админке/письмах: предпочитаем «Имя Фамилия», fallback на @username, потом TG #id.
     */
    protected function findOrCreateByTelegram(
        int $tgId,
        ?string $tgUsername,
        ?string $tgFirstName = null,
        ?string $tgLastName = null,
    ): User {
        $tgUsername = $tgUsername !== null ? trim($tgUsername) ?: null : null;
        $tgFirstName = $tgFirstName !== null ? trim($tgFirstName) ?: null : null;
        $tgLastName = $tgLastName !== null ? trim($tgLastName) ?: null : null;

        return DB::transaction(function () use ($tgId, $tgUsername, $tgFirstName, $tgLastName): User {
            $user = User::query()->where('telegram_id', $tgId)->lockForUpdate()->first();

            if ($user) {
                $dirty = false;
                if ($tgUsername !== null && $user->telegram_username !== $tgUsername) {
                    $user->telegram_username = $tgUsername;
                    $dirty = true;
                }
                if ($tgFirstName !== null && $user->telegram_first_name !== $tgFirstName) {
                    $user->telegram_first_name = $tgFirstName;
                    $dirty = true;
                }
                if ($tgLastName !== null && $user->telegram_last_name !== $tgLastName) {
                    $user->telegram_last_name = $tgLastName;
                    $dirty = true;
                }
                // Синхронизируем `name` для бот-юзеров (сайт-юзеров не трогаем — они
                // сами выбрали имя при регистрации). Если имя в БД ещё было placeholder-ом
                // вида «TG 123» / «@username» — сейчас перезапишем на актуальное.
                if ($dirty && $user->isBotOnly()) {
                    $user->name = $this->buildBotUserName($tgId, $tgUsername, $tgFirstName, $tgLastName);
                }
                if ($dirty) {
                    $user->save();
                }
                return $user;
            }

            $placeholderEmail = 'tg-'.$tgId.'@bot.avavpn.ru';

            $user = User::create([
                'name' => $this->buildBotUserName($tgId, $tgUsername, $tgFirstName, $tgLastName),
                'email' => $placeholderEmail,
                // Случайный «непригодный» пароль: бот-пользователь не может войти по email.
                'password' => \Illuminate\Support\Str::random(40),
                'telegram_id' => $tgId,
                'telegram_username' => $tgUsername,
                'telegram_first_name' => $tgFirstName,
                'telegram_last_name' => $tgLastName,
            ]);

            return $user;
        });
    }

    /**
     * Подпись для `users.name` бот-пользователя. Приоритет:
     * 1) «Имя Фамилия» (Telegram first_name + last_name) — самое осмысленное;
     * 2) @username;
     * 3) TG #id (когда юзер скрыл и username, и имена — редкий, но возможен).
     */
    protected function buildBotUserName(int $tgId, ?string $username, ?string $first, ?string $last): string
    {
        $full = trim((string) $first.' '.(string) $last);
        if ($full !== '') {
            return $full;
        }
        if ($username) {
            return '@'.$username;
        }

        return 'TG '.$tgId;
    }

    protected function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'telegram_username' => $user->telegram_username,
            'telegram_first_name' => $user->telegram_first_name,
            'telegram_last_name' => $user->telegram_last_name,
            'email' => $user->isBotOnly() ? null : $user->email,
            'trial_used' => (bool) $user->trial_used,
        ];
    }

    /**
     * @param  TrialKey|null  $trialKey  форсировать использование конкретного ключа (сразу после выдачи)
     */
    protected function resolveSubscription(User $user, ?TrialKey $trialKey = null): array
    {
        $connectionUri = SharedVpnAccess::connectionUri();

        $activeSubscription = $user->activeSubscriptions()->with('plan')->first();
        if ($activeSubscription) {
            return [
                'status' => 'active',
                'plan_slug' => $activeSubscription->plan?->slug,
                'plan_name' => $activeSubscription->plan?->name,
                'expires_at' => $activeSubscription->expires_at?->toIso8601String(),
                'connection_uri' => $connectionUri,
                'sub_link' => $connectionUri,
            ];
        }

        $trialKey = $trialKey ?? $user->trialKey;
        if ($trialKey) {
            $isActive = $trialKey->isActive();

            return [
                'status' => $isActive ? 'trial' : 'expired',
                'plan_slug' => 'trial',
                'plan_name' => 'Пробный доступ',
                'expires_at' => $trialKey->expires_at?->toIso8601String(),
                'connection_uri' => $isActive ? $connectionUri : null,
                'sub_link' => $isActive ? $connectionUri : null,
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
        $connectionUri = SharedVpnAccess::connectionUri();

        foreach ($user->activeSubscriptions()->with('plan')->get() as $sub) {
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
                'expires_at' => $sub->expires_at?->toIso8601String(),
                'connection_uri' => $connectionUri,
                'sub_link' => $connectionUri,
            ];
        }

        $trialKey = $user->trialKey;
        if ($trialKey && $trialKey->isActive()) {
            $items[] = [
                'key' => 'trial',
                'subscription_id' => null,
                'status' => 'trial',
                'is_trial' => true,
                'plan_slug' => 'trial',
                'plan_name' => 'Пробный доступ',
                'expires_at' => $trialKey->expires_at?->toIso8601String(),
                'connection_uri' => $connectionUri,
                'sub_link' => $connectionUri,
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
            'expires_at' => null,
            'connection_uri' => null,
            'sub_link' => null,
        ];
    }
}
