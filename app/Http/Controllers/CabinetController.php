<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TrialFeedback;
use App\Models\TrialFeedbackRequest;
use App\Services\TrialKeyService;
use App\Support\SharedVpnAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService
    ) {}

    protected function pendingTrialFeedbackRequest(int $userId): ?TrialFeedbackRequest
    {
        $alreadyLeftFeedback = TrialFeedback::query()
            ->where('user_id', $userId)
            ->exists();
        if ($alreadyLeftFeedback) {
            return null;
        }

        return TrialFeedbackRequest::query()
            ->where('user_id', $userId)
            ->whereNull('submitted_at')
            ->latest('id')
            ->first();
    }

    public function subscription(Request $request): View
    {
        $user = $request->user();
        $subscriptions = $user->activeSubscriptions()->with('plan')->get();
        $connectionUri = SharedVpnAccess::connectionUriForUser($user);

        return view('cabinet.subscription', [
            'activeRoute' => 'subscription',
            'user' => $user,
            'subscriptions' => $subscriptions,
            'connectionUri' => $connectionUri,
            'pendingTrialFeedbackRequest' => $this->pendingTrialFeedbackRequest($user->id),
        ]);
    }

    public function trial(Request $request): View
    {
        $user = $request->user();
        $trialKey = $user->trialKey;
        $connectionUri = SharedVpnAccess::trialIsActive($trialKey)
            ? SharedVpnAccess::connectionUri()
            : null;

        return view('cabinet.trial', [
            'activeRoute' => 'trial',
            'user' => $user,
            'trialKey' => $trialKey,
            'connectionUri' => $connectionUri,
            'canUseTrial' => $user->canUseTrial(),
            'pendingTrialFeedbackRequest' => $this->pendingTrialFeedbackRequest($user->id),
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

            return back()->with('success', 'Тестовый период активирован! Скопируйте ссылку подключения ниже.');
        } catch (\Exception $e) {
            return back()->withErrors(['trial' => $e->getMessage()]);
        }
    }

    public function profile(Request $request): View
    {
        return view('cabinet.profile', [
            'activeRoute' => 'profile',
            'user' => $request->user(),
            'pendingTrialFeedbackRequest' => $this->pendingTrialFeedbackRequest($request->user()->id),
        ]);
    }

    public function security(Request $request): View
    {
        return view('cabinet.security', [
            'activeRoute' => 'security',
            'user' => $request->user(),
            'pendingTrialFeedbackRequest' => $this->pendingTrialFeedbackRequest($request->user()->id),
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
        $standardPlans = $plans->where('devices', 2)->sortBy('days')->values();
        $extendedPlans = $plans->where('devices', 5)->sortBy('days')->values();

        return view('cabinet.history', [
            'activeRoute' => 'history',
            'orders' => $orders,
            'plans' => $plans,
            'standardPlans' => $standardPlans,
            'extendedPlans' => $extendedPlans,
            'pendingTrialFeedbackRequest' => $this->pendingTrialFeedbackRequest($user->id),
        ]);
    }
}
