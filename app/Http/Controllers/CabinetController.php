<?php

namespace App\Http\Controllers;

use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TrialFeedback;
use App\Models\TrialFeedbackRequest;
use App\Services\TrialKeyService;
use App\Support\SharedVpnAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CabinetController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService
    ) {}

    public function subscription(Request $request): Response
    {
        $user = $request->user();
        $subscriptions = $user->activeSubscriptions()->with('plan')->get()
            ->map(fn (Subscription $sub) => [
                'id' => $sub->id,
                'planName' => $sub->plan->name,
                'isActive' => $sub->isActive(),
                'isExpired' => $sub->isExpired(),
                'expiresAt' => $sub->expires_at->format('d.m.Y'),
                'daysLeft' => $sub->days_left,
            ])->values();

        $activeTrialKey = SharedVpnAccess::activeTrialKey($user);
        $connectionUri = SharedVpnAccess::connectionUriForUser($user);

        return Inertia::render('Cabinet/Subscription', [
            'subscriptions' => $subscriptions,
            'activeTrial' => $activeTrialKey ? [
                'expiresAt' => $activeTrialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i'),
                'remainingTime' => $activeTrialKey->getRemainingTimeRu(),
            ] : null,
            'connectionUri' => $connectionUri,
        ]);
    }

    public function trial(Request $request): Response
    {
        $user = $request->user();
        $trialKey = $user->trialKey;
        $connectionUri = SharedVpnAccess::connectionUriForUser($user);
        $trialHours = (int) config('vpn.trial.duration_hours', 3);

        $isActive = $trialKey && $trialKey->isActive();
        $timeProgress = 0;
        if ($isActive && $trialKey->activated_at) {
            $totalMinutes = max(1, $trialHours * 60);
            $elapsed = (int) $trialKey->activated_at->diffInMinutes(now());
            $timeProgress = (int) max(0, min(100, round((1 - $elapsed / $totalMinutes) * 100)));
        }
        $showTraffic = $trialKey && $trialKey->total_bytes > 0;

        return Inertia::render('Cabinet/Trial', [
            'trialHours' => $trialHours,
            'canUseTrial' => $user->canUseTrial(),
            'connectionUri' => $connectionUri,
            'trial' => $trialKey ? [
                'isActive' => $isActive,
                'expiresAt' => $trialKey->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i'),
                'remainingTime' => $trialKey->getRemainingTimeRu(),
                'timeProgress' => $timeProgress,
                'trafficProgress' => 100 - $trialKey->getUsagePercent(),
                'showTraffic' => $showTraffic,
                'remainingGb' => $showTraffic ? $trialKey->getRemainingGb() : null,
                'totalGb' => $showTraffic ? $trialKey->getTotalGb() : null,
            ] : null,
        ]);
    }

    public function submitTrialFeedback(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'message' => 'required|string|min:3|max:4000',
        ]);

        TrialFeedback::create([
            'user_id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'telegram_username' => $user->telegram_username,
            'trigger' => 'cabinet_after_trial',
            'message' => trim((string) $data['message']),
        ]);

        TrialFeedbackRequest::query()
            ->where('user_id', $user->id)
            ->whereNull('submitted_at')
            ->update(['submitted_at' => now()]);

        return back()->with('success', 'Спасибо за отзыв! Мы уже передали его команде.');
    }

    public function createTrial(Request $request)
    {
        $user = $request->user();

        if (! $user->canUseTrial()) {
            return back()->withErrors(['trial' => 'Вы уже использовали тестовый период']);
        }

        try {
            $this->trialKeyService->createTrialKey($user);

            return redirect()
                ->route('cabinet.subscription')
                ->with('success', 'Пробная подписка активирована на 3 часа. Скопируйте подписочную ссылку ниже.');
        } catch (\Exception $e) {
            return back()->withErrors(['trial' => $e->getMessage()]);
        }
    }

    public function profile(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Cabinet/Profile', [
            'hasActiveAccess' => $user->activeSubscriptions()->exists()
                || SharedVpnAccess::activeTrialKey($user) !== null,
            'createdAt' => optional($user->created_at)->timezone(config('app.timezone'))->format('d.m.Y') ?? '—',
        ]);
    }

    public function security(Request $request): Response
    {
        return Inertia::render('Cabinet/Security');
    }

    public function history(Request $request): Response
    {
        $user = $request->user();
        $orders = $user->keyOrders()
            ->with('plan')
            ->latest()
            ->paginate(20)
            ->through(fn (KeyOrder $order) => [
                'id' => $order->id,
                'planName' => $order->plan?->name,
                'periodLabel' => $order->plan?->period_label,
                'devices' => $order->plan?->devices,
                'note' => $order->note,
                'amount' => $order->amount ? number_format($order->amount, 0, '', ' ').' ₽' : null,
                'createdAt' => $order->created_at->format('d.m.Y H:i'),
                'status' => $order->status->value ?? (string) $order->status,
            ])
            ->withQueryString();

        $mapPlans = fn ($plans) => $plans->map(fn (Plan $plan) => [
            'id' => $plan->id,
            'periodLabel' => $plan->period_label,
            'trafficGb' => $plan->traffic_gb,
            'formattedPrice' => $plan->formatted_price,
            'discount' => $plan->discount,
        ])->values();

        $plans = Plan::active()->ordered()->get();

        return Inertia::render('Cabinet/History', [
            'orders' => $orders,
            'tiers' => [
                ['name' => 'Стандартный', 'devices' => 2, 'featured' => false, 'plans' => $mapPlans($plans->where('devices', 2)->sortBy('days'))],
                ['name' => 'Расширенный', 'devices' => 5, 'featured' => true, 'plans' => $mapPlans($plans->where('devices', 5)->sortBy('days'))],
                ['name' => 'Премиум', 'devices' => 10, 'featured' => false, 'plans' => $mapPlans($plans->where('devices', 10)->sortBy('days'))],
            ],
        ]);
    }
}
