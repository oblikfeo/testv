<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\KeyOrder;
use App\Models\Plan;
use App\Services\YooKassaService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected YooKassaService $yooKassaService
    ) {}

    public function createPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($request->plan_id);

        $order = KeyOrder::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => OrderStatus::Pending,
            'purchase_source' => 'web',
            'amount' => $plan->price,
            'note' => "Оплата тарифа {$plan->name}",
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
