<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionKey;
use App\Services\KeyPoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionKeyController extends Controller
{
    public function index(Request $request): View
    {
        $keys = $request->user()
            ->subscriptionKeys()
            ->with('pair')
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('subscription-keys.index', [
            'keys' => $keys,
            'activeRoute' => 'keys',
        ]);
    }

    public function issue(Request $request, KeyPoolService $pool): RedirectResponse
    {
        try {
            $pool->issueKeyToUser($request->user());
        } catch (\Throwable $e) {
            return back()->withErrors(['issue' => $e->getMessage()]);
        }

        return back()->with('status', 'key-issued');
    }

    public function activate(Request $request, SubscriptionKey $subscription_key): RedirectResponse
    {
        abort_unless($subscription_key->user_id === $request->user()->id, 403);

        app(KeyPoolService::class)->markActivatedIfPending($subscription_key);

        return back()->with('status', 'key-activated');
    }
}
