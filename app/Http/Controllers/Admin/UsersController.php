<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionManager;
use App\Services\TrialKeyService;
use App\Support\SharedVpnAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function __construct(
        protected SubscriptionManager $subscriptions,
        protected TrialKeyService $trialKeys,
    ) {}

    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->with(['activeSubscriptions', 'trialKey'])
            ->withCount('subscriptions')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('telegram_username', 'like', "%{$q}%")
                        ->orWhere('telegram_id', $q)
                        ->orWhere('id', $q);
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'contact' => $u->isBotOnly() ? ('@'.($u->telegram_username ?? $u->telegram_id)) : $u->email,
                'isTelegram' => $u->telegram_id !== null,
                'access' => $this->accessStatus($u),
                'createdAt' => $u->created_at?->format('d.m.Y'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => ['q' => $q],
        ]);
    }

    public function show(User $user): Response
    {
        $user->load([
            'subscriptions.plan',
            'keyOrders.plan',
            'trialKey',
            'supportTickets',
        ]);

        $trial = $user->trialKey;

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->isBotOnly() ? null : $user->email,
                'emailVerified' => $user->email_verified_at !== null,
                'telegramId' => $user->telegram_id,
                'telegramUsername' => $user->telegram_username,
                'trialUsed' => (bool) $user->trial_used,
                'createdAt' => $user->created_at?->format('d.m.Y H:i'),
                'access' => $this->accessStatus($user),
                'subLink' => $user->vpn_sub_id
                    ? route('subscription.show', ['subId' => $user->vpn_sub_id], true)
                    : null,
            ],
            'subscriptions' => $user->subscriptions
                ->sortByDesc('expires_at')
                ->values()
                ->map(fn (Subscription $s) => [
                    'id' => $s->id,
                    'plan' => $s->plan?->name ?? '—',
                    'status' => $s->status,
                    'isActive' => $s->isActive(),
                    'maxDevices' => (int) $s->max_devices,
                    'startsAt' => $s->starts_at?->format('d.m.Y'),
                    'expiresAt' => $s->expires_at?->format('d.m.Y H:i'),
                    'daysLeft' => $s->isActive() ? $s->days_left : 0,
                    'source' => $s->purchase_source,
                ]),
            'orders' => $user->keyOrders
                ->sortByDesc('id')
                ->values()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'plan' => $o->plan?->name ?? '—',
                    'amount' => (int) $o->amount,
                    'status' => $o->status?->value,
                    'paymentStatus' => $o->payment_status,
                    'method' => $o->payment_method,
                    'source' => $o->purchase_source,
                    'createdAt' => $o->created_at?->format('d.m.Y H:i'),
                ]),
            'trial' => $trial ? [
                'expiresAt' => $trial->expires_at?->format('d.m.Y H:i'),
                'isActive' => $trial->isActive(),
                'usedGb' => $trial->getUsedGb(),
                'totalGb' => $trial->getTotalGb(),
                'percent' => $trial->getUsagePercent(),
                'remaining' => $trial->getRemainingTimeRu(),
            ] : null,
            'tickets' => $user->supportTickets
                ->sortByDesc('id')
                ->values()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'subject' => $t->subject,
                    'status' => $t->status,
                    'category' => $t->categoryLabel(),
                    'lastMessageAt' => $t->last_message_at?->format('d.m.Y H:i'),
                ]),
            'plans' => Plan::query()->active()->ordered()->get()->map(fn (Plan $p) => [
                'id' => $p->id,
                'label' => "{$p->name} · {$p->devices} устр. · {$p->days} дн. · {$p->price} ₽",
            ]),
        ]);
    }

    public function grantSubscription(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $sub = $this->subscriptions->createForPlan($user, $plan, 'admin');

        return back()->with('success', "Выдана подписка «{$plan->name}» до ".$sub->expires_at->format('d.m.Y').'.');
    }

    public function extendSubscription(Request $request, User $user, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $user->id, 404);

        $data = $request->validate([
            'days' => 'required|integer|min:1|max:3650',
        ]);

        $sub = $this->subscriptions->extendByDays($subscription, (int) $data['days']);

        return back()->with('success', "Подписка #{$subscription->id} продлена до ".$sub->expires_at->format('d.m.Y').'.');
    }

    public function expireSubscription(User $user, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $user->id, 404);

        $this->subscriptions->expire($subscription);

        return back()->with('success', "Подписка #{$subscription->id} деактивирована.");
    }

    public function issueTrial(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'hours' => 'required|integer|min:1|max:168',
            'gb' => 'required|integer|min:0|max:100',
        ]);

        try {
            $this->trialKeys->createTrialKeyForAdmin($user, (int) $data['hours'], (int) $data['gb']);

            return back()->with('success', 'Триал выдан.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revokeTrial(User $user): RedirectResponse
    {
        $trial = $user->trialKey;

        if (! $trial) {
            return back()->with('error', 'У пользователя нет триала.');
        }

        $this->trialKeys->revokeTrialKey($trial);

        return back()->with('success', 'Триал отозван.');
    }

    private function accessStatus(User $user): string
    {
        $hasActive = $user->relationLoaded('activeSubscriptions')
            ? $user->activeSubscriptions->isNotEmpty()
            : $user->activeSubscriptions()->exists();

        if ($hasActive) {
            return 'active';
        }

        $trial = $user->trialKey;
        if ($trial && $trial->isActive()) {
            return 'trial';
        }

        $ever = $user->subscriptions_count
            ?? ($user->relationLoaded('subscriptions') ? $user->subscriptions->count() : $user->subscriptions()->count());

        if ($ever > 0 || $trial !== null) {
            return 'expired';
        }

        return 'none';
    }
}
