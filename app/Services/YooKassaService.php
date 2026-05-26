<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\SaleKey;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YooKassaService
{
    protected string $shopId;

    protected string $secretKey;

    protected string $apiUrl = 'https://api.yookassa.ru/v3';

    public function __construct(
        protected TelegramBotNotifier $tgNotifier
    ) {
        $this->shopId = config('yookassa.shop_id');
        $this->secretKey = config('yookassa.secret_key');
    }

    /**
     * @param  string|null  $returnUrlOverride  если передано — заменяет `yookassa.return_url`
     *                                          для этого конкретного платежа (используется ботом,
     *                                          чтобы после YooKassa юзера возвращало в чат Telegram).
     */
    public function createPayment(User $user, Plan $plan, KeyOrder $order, ?string $returnUrlOverride = null): ?array
    {
        $idempotenceKey = Str::uuid()->toString();

        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->withHeaders([
                'Idempotence-Key' => $idempotenceKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->apiUrl}/payments", [
                'amount' => [
                    'value' => number_format($plan->price, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => $returnUrlOverride !== null && $returnUrlOverride !== ''
                        ? $returnUrlOverride
                        : $this->paymentReturnUrl($order),
                ],
                'capture' => true,
                'description' => "Оплата тарифа {$plan->name} - {$plan->period_label}",
                'receipt' => [
                    'customer' => [
                        'email' => $user->email,
                    ],
                    'items' => [
                        [
                            'description' => "VPN подписка: {$plan->name} ({$plan->period_label})",
                            'quantity' => '1.00',
                            'amount' => [
                                'value' => number_format($plan->price, 2, '.', ''),
                                'currency' => 'RUB',
                            ],
                            'vat_code' => 1,
                            'payment_mode' => 'full_payment',
                            'payment_subject' => 'service',
                        ],
                    ],
                ],
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json();
            Log::info('YooKassa payment created', [
                'payment_id' => $data['id'],
                'order_id' => $order->id,
            ]);
            return $data;
        }

        Log::error('YooKassa payment creation failed', [
            'status' => $response->status(),
            'body' => $response->body(),
            'order_id' => $order->id,
        ]);

        return null;
    }

    public function getPayment(string $paymentId): ?array
    {
        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->get("{$this->apiUrl}/payments/{$paymentId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('YooKassa get payment failed', [
            'payment_id' => $paymentId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    public function processWebhook(array $data): bool
    {
        $event = $data['event'] ?? null;
        $object = $data['object'] ?? null;

        if (!$event || !$object) {
            Log::warning('YooKassa webhook: invalid data', $data);
            return false;
        }

        $paymentId = $object['id'] ?? null;
        if ($event === 'refund.succeeded') {
            // For refund webhooks YooKassa sends refund object (id/status/payment_id),
            // not payment metadata. We must resolve order by original payment_id.
            $paymentId = $object['payment_id'] ?? null;
            if (! $paymentId) {
                Log::warning('YooKassa webhook refund: missing payment_id', $data);

                return false;
            }

            return $this->handleRefundSucceeded($paymentId, $object);
        }

        $status = $object['status'] ?? null;
        $metadata = $object['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if (!$paymentId || !$orderId) {
            Log::warning('YooKassa webhook: missing payment_id or order_id', $data);
            return false;
        }

        $order = KeyOrder::find($orderId);
        if (!$order) {
            Log::error('YooKassa webhook: order not found', ['order_id' => $orderId]);
            return false;
        }

        $order->update([
            'payment_status' => $status,
        ]);

        if ($event === 'payment.succeeded' && $status === 'succeeded') {
            return $this->handleSuccessfulPayment($order, $object);
        }

        if ($event === 'payment.canceled') {
            $order->update(['status' => OrderStatus::Cancelled]);
        }

        return true;
    }

    public function syncRefundStatus(KeyOrder $order): bool
    {
        if (! $order->payment_id) {
            return false;
        }

        $payment = $this->getPayment($order->payment_id);
        if (! $payment) {
            return false;
        }

        if (! $this->isFullyRefundedPayment($payment)) {
            return false;
        }

        return $this->handleRefundSucceeded($order->payment_id, [
            'id' => $payment['id'] ?? null,
            'source' => 'payment_poll',
        ]);
    }

    protected function handleRefundSucceeded(string $paymentId, array $refundData): bool
    {
        $order = KeyOrder::query()->where('payment_id', $paymentId)->first();
        if (! $order) {
            Log::error('YooKassa refund: order not found by payment_id', [
                'payment_id' => $paymentId,
                'refund_id' => $refundData['id'] ?? null,
            ]);

            return false;
        }

        $order->update([
            'status' => OrderStatus::Cancelled,
            'payment_status' => 'refunded',
        ]);

        $subscriptionId = $order->purchase_action === 'renew_subscription' && $order->target_subscription_id
            ? (int) $order->target_subscription_id
            : null;

        if (! $subscriptionId && $order->sale_key_id) {
            $subscriptionId = (int) (SaleKey::query()
                ->where('id', $order->sale_key_id)
                ->value('subscription_id') ?? 0);
        }

        if (! $subscriptionId) {
            $subscriptionId = (int) (Subscription::query()
                ->where('user_id', $order->user_id)
                ->where('plan_id', $order->plan_id)
                ->where('created_at', '>=', $order->created_at?->subMinutes(5) ?? now()->subMinutes(5))
                ->orderByDesc('id')
                ->value('id') ?? 0);
        }

        if ($subscriptionId) {
            Subscription::query()
                ->where('id', $subscriptionId)
                ->update([
                    'status' => 'expired',
                    'expires_at' => now(),
                ]);
        }

        Log::info('YooKassa refund processed', [
            'order_id' => $order->id,
            'payment_id' => $paymentId,
            'refund_id' => $refundData['id'] ?? null,
        ]);

        return true;
    }

    protected function handleSuccessfulPayment(KeyOrder $order, array $paymentData): bool
    {
        $order->refresh();

        if ($order->status === OrderStatus::Fulfilled) {
            return true;
        }

        $order->update([
            'status' => OrderStatus::Fulfilled,
            'paid_at' => now(),
            'payment_method' => $paymentData['payment_method']['type'] ?? 'unknown',
        ]);

        $plan = $order->plan;
        $user = $order->user;

        if ($plan && $user) {
            if ($order->purchase_action === 'renew_subscription' && $order->target_subscription_id) {
                $targetSubscription = $user->subscriptions()
                    ->where('id', $order->target_subscription_id)
                    ->first();

                if (! $targetSubscription) {
                    Log::error('Renew target subscription not found', [
                        'order_id' => $order->id,
                        'target_subscription_id' => $order->target_subscription_id,
                    ]);

                    return false;
                }

                $baseDate = $targetSubscription->expires_at && $targetSubscription->expires_at->isFuture()
                    ? $targetSubscription->expires_at->copy()
                    : now();

                $targetSubscription->update([
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'expires_at' => $baseDate->addDays($plan->days),
                    'max_devices' => $plan->devices,
                    'purchase_source' => $order->purchase_source ?? 'unknown',
                ]);
                $subscription = $targetSubscription->fresh();
            } else {
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'purchase_source' => $order->purchase_source ?? 'unknown',
                    'max_devices' => $plan->devices,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays($plan->days),
                ]);
            }

            // Если это бот-пользователь — пушим уведомление прямо в Telegram,
            // чтобы не ждать нажатия «Я оплатил — проверить». Ошибка не роняет вебхук.
            if ($user->telegram_id) {
                try {
                    $untilHuman = $subscription->expires_at?->timezone(config('app.timezone', 'UTC'))->format('d.m.Y H:i') ?? '';
                    $text = "✅ <b>Оплата получена!</b>\n"
                        . "Тариф: <b>{$plan->name}</b> ({$plan->devices} устр.)\n"
                        . "Активна до: <code>{$untilHuman}</code>\n\n"
                        . "Нажмите «🔐 Подключиться» в меню — ссылка и QR уже готовы.";
                    $this->tgNotifier->sendMessage((int) $user->telegram_id, $text);
                } catch (\Throwable $e) {
                    Log::warning('Telegram notify after payment failed', [
                        'order_id' => $order->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('YooKassa payment successful', [
            'order_id' => $order->id,
            'payment_id' => $order->payment_id,
        ]);

        return true;
    }

    protected function paymentReturnUrl(KeyOrder $order): string
    {
        $configured = config('yookassa.return_url');
        if (is_string($configured) && $configured !== '' && $this->isInvalidCabinetReturnPath($configured)) {
            $configured = '';
        }
        if (is_string($configured) && $configured !== '') {
            $base = preg_match('#^https?://#i', $configured)
                ? rtrim($configured, '/')
                : url($configured);

            return $base . (str_contains($base, '?') ? '&' : '?') . http_build_query(['order_id' => $order->id]);
        }

        return route('cabinet.subscription', ['order_id' => $order->id], true);
    }

    private function isInvalidCabinetReturnPath(string $value): bool
    {
        if (str_contains($value, '://')) {
            $path = (string) (parse_url($value, PHP_URL_PATH) ?? '');

            return rtrim($path, '/') === '/cabinet/subscription';
        }

        return rtrim($value, '/') === '/cabinet/subscription';
    }

    public function checkPaymentStatus(KeyOrder $order): ?string
    {
        if (!$order->payment_id) {
            return null;
        }

        $payment = $this->getPayment($order->payment_id);
        if (!$payment) {
            return null;
        }

        $status = $payment['status'] ?? null;

        if ($this->isFullyRefundedPayment($payment)) {
            $this->handleRefundSucceeded($order->payment_id, [
                'id' => $payment['id'] ?? null,
                'source' => 'payment_status_check',
            ]);
            $order->refresh();
            return $order->payment_status;
        }
        
        if ($status && $status !== $order->payment_status) {
            $order->update(['payment_status' => $status]);

            if ($status === 'succeeded' && $order->status !== OrderStatus::Fulfilled) {
                $this->handleSuccessfulPayment($order, $payment);
            }
        }

        return $status;
    }

    private function isFullyRefundedPayment(array $payment): bool
    {
        $amount = (float) ($payment['amount']['value'] ?? 0);
        $refunded = (float) ($payment['refunded_amount']['value'] ?? 0);

        if ($amount <= 0 || $refunded <= 0) {
            return false;
        }

        return $refunded >= $amount;
    }
}
