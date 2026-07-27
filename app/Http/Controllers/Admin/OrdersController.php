<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\KeyOrder;
use App\Services\YooKassaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class OrdersController extends Controller
{
    public function __construct(
        protected YooKassaService $yooKassa,
    ) {}

    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $source = (string) $request->query('source', '');

        $base = KeyOrder::query()
            ->with(['user:id,name,email,telegram_username', 'plan:id,name'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('payment_id', 'like', "%{$q}%")
                        ->orWhere('id', $q)
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('email', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%")
                            ->orWhere('telegram_username', 'like', "%{$q}%"));
                });
            })
            ->when(in_array($status, ['pending', 'fulfilled', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->when($source !== '', fn ($query) => $query->where('purchase_source', $source));

        $orders = (clone $base)
            ->latest('id')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (KeyOrder $o) => [
                'id' => $o->id,
                'user' => $o->user ? [
                    'id' => $o->user->id,
                    'label' => $o->user->isBotOnly() ? ('@'.($o->user->telegram_username ?? $o->user->telegram_id)) : ($o->user->name ?: $o->user->email),
                ] : null,
                'plan' => $o->plan?->name ?? '—',
                'amount' => (int) $o->amount,
                'status' => $o->status?->value,
                'paymentStatus' => $o->payment_status,
                'method' => $o->payment_method,
                'source' => $o->purchase_source,
                'action' => $o->purchase_action,
                'paymentId' => $o->payment_id,
                'createdAt' => $o->created_at?->format('d.m.Y H:i'),
                'paidAt' => $o->paid_at?->format('d.m.Y H:i'),
                'canSync' => $o->payment_id !== null && $o->status !== OrderStatus::Fulfilled,
            ]);

        $now = Carbon::now();
        $revenue = [
            'today' => (int) KeyOrder::query()->where('status', OrderStatus::Fulfilled)->where('paid_at', '>=', $now->copy()->startOfDay())->sum('amount'),
            'month' => (int) KeyOrder::query()->where('status', OrderStatus::Fulfilled)->where('paid_at', '>=', $now->copy()->startOfMonth())->sum('amount'),
            'filtered' => (int) (clone $base)->where('status', OrderStatus::Fulfilled)->sum('amount'),
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => ['q' => $q, 'status' => $status, 'source' => $source],
            'revenue' => $revenue,
            'sources' => KeyOrder::query()->select('purchase_source')->distinct()->pluck('purchase_source')->filter()->values(),
        ]);
    }

    public function sync(KeyOrder $order): RedirectResponse
    {
        if (! $order->payment_id) {
            return back()->with('error', 'У заказа нет payment_id для синхронизации.');
        }

        try {
            $status = $this->yooKassa->checkPaymentStatus($order);

            return back()->with('success', "Статус синхронизирован: {$status}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Ошибка синхронизации: '.$e->getMessage());
        }
    }
}
