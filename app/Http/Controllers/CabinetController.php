<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\SaleKey;
use App\Services\TrialKeyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService
    ) {}
    public function subscription(Request $request): View
    {
        $user = $request->user();
        $subscriptions = $user->activeSubscriptions()->with('plan')->get();

        $saleKeys = [];
        if ($subscriptions->isNotEmpty()) {
            $saleKeys = SaleKey::query()
                ->whereIn('subscription_id', $subscriptions->pluck('id'))
                ->where('status', 'active')
                ->get()
                ->keyBy('subscription_id');
        }

        return view('cabinet.subscription', [
            'activeRoute' => 'subscription',
            'user' => $user,
            'subscriptions' => $subscriptions,
            'saleKeys' => $saleKeys,
        ]);
    }

    public function trial(Request $request): View
    {
        $user = $request->user();
        $trialKey = $user->trialKey;
        $trialDevices = collect();

        if ($trialKey) {
            $this->trialKeyService->syncTrafficFromPanel($trialKey);
            $trialKey->refresh();
            $trialDevices = $trialKey->devices()->latest()->get();
        }

        return view('cabinet.trial', [
            'activeRoute' => 'trial',
            'user' => $user,
            'trialKey' => $trialKey,
            'trialDevices' => $trialDevices,
            'canUseTrial' => $user->canUseTrial(),
        ]);
    }

    public function deleteTrialDevice(Request $request, int $deviceId)
    {
        $user = $request->user();
        $trialKey = $user->trialKey;

        if (! $trialKey) {
            return back()->withErrors(['device' => 'Тестовый ключ не найден']);
        }

        $device = $trialKey->devices()->find($deviceId);
        if (! $device) {
            return back()->withErrors(['device' => 'Устройство не найдено']);
        }

        $device->delete();

        return back()->with('success', 'Устройство удалено');
    }

    public function createTrial(Request $request)
    {
        $user = $request->user();

        if (!$user->canUseTrial()) {
            return back()->withErrors(['trial' => 'Вы уже использовали тестовый период']);
        }

        try {
            $trialKey = $this->trialKeyService->createTrialKey($user);
            return back()->with('success', 'Тестовый ключ успешно создан!');
        } catch (\Exception $e) {
            return back()->withErrors(['trial' => $e->getMessage()]);
        }
    }

    public function profile(Request $request): View
    {
        return view('cabinet.profile', [
            'activeRoute' => 'profile',
            'user' => $request->user(),
        ]);
    }

    public function security(Request $request): View
    {
        return view('cabinet.security', [
            'activeRoute' => 'security',
            'user' => $request->user(),
        ]);
    }

    public function history(Request $request): View
    {
        $user = $request->user();
        $orders = $user->keyOrders()
            ->with('plan')
            ->latest()
            ->paginate(20);

        $plans = Plan::active()->ordered()->get();
        return view('cabinet.history', [
            'activeRoute' => 'history',
            'orders' => $orders,
            'plans' => $plans,
        ]);
    }

    public function devices(Request $request): View
    {
        $user = $request->user();
        $subscriptions = $user->activeSubscriptions()->with(['plan', 'devices' => function ($query) {
            $query->latest();
        }])->get();

        $saleKeys = [];
        if ($subscriptions->isNotEmpty()) {
            $saleKeys = SaleKey::query()
                ->whereIn('subscription_id', $subscriptions->pluck('id'))
                ->where('status', 'active')
                ->get()
                ->keyBy('subscription_id');
        }

        return view('cabinet.devices', [
            'activeRoute' => 'devices',
            'user' => $user,
            'subscriptions' => $subscriptions,
            'saleKeys' => $saleKeys,
        ]);
    }
}
