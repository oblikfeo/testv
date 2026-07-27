<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionsController extends Controller
{
    public function __construct(
        protected SubscriptionManager $subscriptions,
    ) {}

    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'active');

        $subscriptions = Subscription::query()
            ->with(['user:id,name,email,telegram_username', 'plan:id,name'])
            ->when($q !== '', fn ($query) => $query->whereHas('user', fn ($u) => $u
                ->where('email', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->orWhere('telegram_username', 'like', "%{$q}%")))
            ->when($status === 'active', fn ($query) => $query->active())
            ->when($status === 'inactive', fn ($query) => $query->where(fn ($w) => $w
                ->where('status', '!=', 'active')
                ->orWhere('expires_at', '<=', now())))
            ->latest('id')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Subscription $s) => [
                'id' => $s->id,
                'user' => $s->user ? [
                    'id' => $s->user->id,
                    'label' => $s->user->isBotOnly() ? ('@'.($s->user->telegram_username ?? $s->user->telegram_id)) : ($s->user->name ?: $s->user->email),
                ] : null,
                'plan' => $s->plan?->name ?? '—',
                'maxDevices' => (int) $s->max_devices,
                'status' => $s->status,
                'isActive' => $s->isActive(),
                'expiresAt' => $s->expires_at?->format('d.m.Y H:i'),
                'daysLeft' => $s->isActive() ? $s->days_left : 0,
                'source' => $s->purchase_source,
            ]);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => ['q' => $q, 'status' => $status],
            'counts' => [
                'active' => Subscription::query()->active()->count(),
                'total' => Subscription::query()->count(),
            ],
        ]);
    }

    public function extend(Request $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validate(['days' => 'required|integer|min:1|max:3650']);

        $sub = $this->subscriptions->extendByDays($subscription, (int) $data['days']);

        return back()->with('success', "Подписка #{$subscription->id} продлена до ".$sub->expires_at->format('d.m.Y').'.');
    }

    public function expire(Subscription $subscription): RedirectResponse
    {
        $this->subscriptions->expire($subscription);

        return back()->with('success', "Подписка #{$subscription->id} деактивирована.");
    }
}
