<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SharedVpnAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionStatusApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required_without:user_id|nullable|email',
            'user_id' => 'required_without:email|nullable|integer',
        ]);

        $user = null;
        if (! empty($data['user_id'])) {
            $user = User::query()->find($data['user_id']);
        } elseif (! empty($data['email'])) {
            $user = User::query()->where('email', $data['email'])->first();
        }

        if (! $user) {
            return response()->json(['ok' => false, 'message' => 'Пользователь не найден'], 404);
        }

        $subscription = $user->activeSubscription;
        $connectionUri = SharedVpnAccess::connectionUriForUser($user);

        return response()->json([
            'ok' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'connection_uri' => $connectionUri,
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan?->name,
                'expires_at' => $subscription->expires_at?->toIso8601String(),
                'is_active' => $subscription->isActive(),
            ] : null,
            'trial' => $user->trialKey ? [
                'expires_at' => $user->trialKey->expires_at?->toIso8601String(),
                'is_active' => $user->trialKey->isActive(),
            ] : null,
        ]);
    }
}
