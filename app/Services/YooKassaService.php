<?php

namespace App\Services;

use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YooKassaService
{
    protected string $shopId;
    protected string $secretKey;
    protected string $apiUrl = 'https://api.yookassa.ru/v3';

    public function __construct()
    {
        $this->shopId = config('yookassa.shop_id');
        $this->secretKey = config('yookassa.secret_key');
    }

    public function createPayment(User $user, Plan $plan, KeyOrder $order): ?array
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
                    'return_url' => url(config('yookassa.return_url')) . '?order_id=' . $order->id,
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
            $order->update(['status' => 'cancelled']);
        }

        return true;
    }

    protected function handleSuccessfulPayment(KeyOrder $order, array $paymentData): bool
    {
        $order->update([
            'status' => 'fulfilled',
            'paid_at' => now(),
            'payment_method' => $paymentData['payment_method']['type'] ?? 'unknown',
        ]);

        $plan = $order->plan;
        $user = $order->user;

        if ($plan && $user) {
            $existingSubscription = $user->activeSubscription;
            
            if ($existingSubscription && $existingSubscription->plan_id === $plan->id) {
                $existingSubscription->update([
                    'expires_at' => $existingSubscription->expires_at->addDays($plan->days),
                ]);
            } else {
                \App\Models\Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'max_devices' => $plan->devices,
                    'starts_at' => now(),
                    'expires_at' => now()->addDays($plan->days),
                ]);
            }
        }

        Log::info('YooKassa payment successful', [
            'order_id' => $order->id,
            'payment_id' => $order->payment_id,
        ]);

        return true;
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
        
        if ($status && $status !== $order->payment_status) {
            $order->update(['payment_status' => $status]);

            if ($status === 'succeeded' && $order->status !== 'fulfilled') {
                $this->handleSuccessfulPayment($order, $payment);
            }
        }

        return $status;
    }
}
