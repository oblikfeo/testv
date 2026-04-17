<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleKey;
use App\Models\User;
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
        $saleKey = $subscription
            ? SaleKey::query()->where('subscription_id', $subscription->id)->first()
            : null;

        return response()->json([
            'ok' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan' => $subscription->plan?->name,
                'expires_at' => $subscription->expires_at?->toIso8601String(),
                'max_devices' => $subscription->max_devices,
                'devices_count' => $subscription->devices_count,
                'is_active' => $subscription->isActive(),
            ] : null,
            'sale_key' => $saleKey ? [
                'sub_id' => $saleKey->sub_id,
                'subscription_url' => url('/sub/'.$saleKey->sub_id),
                'is_sponsor' => $saleKey->is_sponsor,
                'expires_at' => $saleKey->expires_at?->toIso8601String(),
            ] : null,
        ]);
    }
}
