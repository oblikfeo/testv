<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleKey;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    public function __construct(
        protected DeviceService $deviceService
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sub_id' => 'required|string|max:64',
            'hwid' => 'required|string|max:512',
        ]);

        $saleKey = SaleKey::query()->where('sub_id', $data['sub_id'])->first();
        if (! $saleKey) {
            return response()->json(['ok' => false, 'message' => 'Подписка не найдена'], 404);
        }

        $subscription = $saleKey->subscription;
        if (! $subscription || ! $subscription->isActive()) {
            return response()->json(['ok' => false, 'message' => 'Подписка не активна'], 403);
        }

        $device = $this->deviceService->registerDevice(
            $subscription,
            $data['hwid'],
            $request->userAgent(),
            $request->ip()
        );

        if (! $device) {
            return response()->json(['ok' => false, 'message' => 'Лимит устройств'], 422);
        }

        return response()->json([
            'ok' => true,
            'device_id' => $device->id,
        ]);
    }

    public function validateDevice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sub_id' => 'required|string|max:64',
            'hwid' => 'required|string|max:512',
        ]);

        $saleKey = SaleKey::query()->where('sub_id', $data['sub_id'])->first();
        if (! $saleKey) {
            return response()->json(['ok' => false], 404);
        }

        $subscription = $saleKey->subscription;
        if (! $subscription) {
            return response()->json(['ok' => false], 404);
        }

        $ok = $this->deviceService->validateDevice($subscription, $data['hwid']);

        return response()->json(['ok' => $ok]);
    }
}
