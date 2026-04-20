<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleKey;
use App\Models\TrialDevice;
use App\Models\TrialKey;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    protected const TRIAL_MAX_DEVICES = 1;

    public function __construct(
        protected DeviceService $deviceService
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sub_id' => 'required|string|max:64',
            'hwid' => 'nullable|string|max:512',
        ]);

        $hwid = $data['hwid'] ?? $this->deviceService->extractHwidFromRequest($request);
        if (! is_string($hwid) || $hwid === '') {
            return response()->json(['ok' => false, 'message' => 'Укажите hwid (тело запроса или заголовок X-HWID / X-Device-ID)'], 422);
        }

        $trialKey = TrialKey::query()->where('sub_id', $data['sub_id'])->first();
        if ($trialKey) {
            return $this->registerTrialDevice($trialKey, $hwid, $request);
        }

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
            $hwid,
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

    protected function registerTrialDevice(TrialKey $trialKey, string $hwid, Request $request): JsonResponse
    {
        if (! $trialKey->isActive()) {
            return response()->json(['ok' => false, 'message' => 'Тестовый период истёк'], 403);
        }

        $existingDevice = TrialDevice::where('trial_key_id', $trialKey->id)
            ->where('hwid', $hwid)
            ->first();

        if ($existingDevice) {
            $existingDevice->updateActivity($request->ip());
            return response()->json(['ok' => true, 'device_id' => $existingDevice->id]);
        }

        $currentDevices = TrialDevice::where('trial_key_id', $trialKey->id)->count();
        if ($currentDevices >= self::TRIAL_MAX_DEVICES) {
            return response()->json(['ok' => false, 'message' => 'Лимит устройств для тестового периода'], 422);
        }

        $device = TrialDevice::create([
            'trial_key_id' => $trialKey->id,
            'hwid' => $hwid,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'last_active_at' => now(),
        ]);

        return response()->json(['ok' => true, 'device_id' => $device->id]);
    }

    public function validateDevice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sub_id' => 'required|string|max:64',
            'hwid' => 'nullable|string|max:512',
        ]);

        $hwid = $data['hwid'] ?? $this->deviceService->extractHwidFromRequest($request);
        if (! is_string($hwid) || $hwid === '') {
            return response()->json(['ok' => false, 'message' => 'Укажите hwid'], 422);
        }

        $trialKey = TrialKey::query()->where('sub_id', $data['sub_id'])->first();
        if ($trialKey) {
            return $this->validateTrialDevice($trialKey, $hwid);
        }

        $saleKey = SaleKey::query()->where('sub_id', $data['sub_id'])->first();
        if (! $saleKey) {
            return response()->json(['ok' => false], 404);
        }

        $subscription = $saleKey->subscription;
        if (! $subscription) {
            return response()->json(['ok' => false], 404);
        }

        $ok = $this->deviceService->validateDevice($subscription, $hwid);

        return response()->json(['ok' => $ok]);
    }

    protected function validateTrialDevice(TrialKey $trialKey, string $hwid): JsonResponse
    {
        if (! $trialKey->isActive()) {
            return response()->json(['ok' => false]);
        }

        $device = TrialDevice::where('trial_key_id', $trialKey->id)
            ->where('hwid', $hwid)
            ->first();

        if ($device) {
            $device->updateActivity();
            return response()->json(['ok' => true]);
        }

        $currentDevices = TrialDevice::where('trial_key_id', $trialKey->id)->count();
        if ($currentDevices < self::TRIAL_MAX_DEVICES) {
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false]);
    }
}
