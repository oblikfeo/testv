<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\YooKassaService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected YooKassaService $yooKassaService
    ) {}

    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'purchase_action' => 'nullable|string|in:new_purchase,renew_subscription',
            'target_subscription_id' => 'nullable|integer|exists:subscriptions,id',
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($data['plan_id']);
        $purchaseAction = $data['purchase_action'] ?? null;
        $targetSubscriptionId = null;

        if ($purchaseAction === null) {
            $compatibleSubscriptions = $user->subscriptions()
                ->with('plan')
                ->where('max_devices', $plan->devices)
                ->orderByDesc('expires_at')
                ->get();

            if ($compatibleSubscriptions->isNotEmpty()) {
                $choicePayload = [
                    'plan' => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'period_label' => $plan->period_label,
                        'price' => $plan->formatted_price,
                        'devices' => (int) $plan->devices,
                    ],
                    'subscriptions' => $compatibleSubscriptions->map(fn (Subscription $sub) => [
                        'id' => $sub->id,
                        'plan_name' => $sub->plan?->name ?? 'Без тарифа',
                        'expires_at' => optional($sub->expires_at)->format('d.m.Y'),
                        'status' => $sub->status,
                        'max_devices' => (int) $sub->max_devices,
                    ])->values()->all(),
                ];

                return back()->with('purchase_choice', $choicePayload);
            }

            $purchaseAction = 'new_purchase';
        }

        if ($purchaseAction === 'renew_subscription') {
            $targetSubscriptionId = (int) ($data['target_subscription_id'] ?? 0);
            if ($targetSubscriptionId <= 0) {
                return back()->withErrors(['payment' => 'Для продления выберите подписку.']);
            }

            $targetSubscription = Subscription::query()
                ->where('id', $targetSubscriptionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $targetSubscription) {
                return back()->withErrors(['payment' => 'Подписка для продления не найдена.']);
            }

            if ((int) $targetSubscription->max_devices !== (int) $plan->devices) {
                return back()->withErrors(['payment' => 'Продление возможно только для подписок с тем же количеством устройств.']);
            }
        }

        $order = KeyOrder::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => OrderStatus::Pending,
            'purchase_source' => 'web',
            'purchase_action' => $purchaseAction,
            'target_subscription_id' => $targetSubscriptionId,
            'amount' => $plan->price,
            'note' => $purchaseAction === 'renew_subscription'
                ? "Продление подписки #{$targetSubscriptionId} ({$plan->name})"
                : "Покупка новой подписки {$plan->name}",
        ]);

        $payment = $this->yooKassaService->createPayment($user, $plan, $order);

        if (!$payment) {
            $order->update(['status' => OrderStatus::Cancelled]);
            return back()->withErrors(['payment' => 'Не удалось создать платёж. Попробуйте позже.']);
        }

        $order->update([
            'payment_id' => $payment['id'],
            'payment_status' => $payment['status'],
        ]);

        $confirmationUrl = $payment['confirmation']['confirmation_url'] ?? null;
        
        if ($confirmationUrl) {
            return redirect($confirmationUrl);
        }

        return back()->withErrors(['payment' => 'Не удалось получить ссылку на оплату.']);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        
        $success = $this->yooKassaService->processWebhook($data);

        return response()->json(['success' => $success]);
    }

    public function checkStatus(Request $request)
    {
        $orderId = $request->query('order_id');
        
        if (!$orderId) {
            return response()->json(['error' => 'Order ID required'], 400);
        }

        $order = KeyOrder::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $status = $this->yooKassaService->checkPaymentStatus($order);

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'payment_status' => $status ?? $order->payment_status,
        ]);
    }
}
