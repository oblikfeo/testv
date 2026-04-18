<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DeviceService
{
    public function registerDevice(
        Subscription $subscription,
        string $hwid,
        ?string $userAgent = null,
        ?string $ipAddress = null
    ): ?Device {
        $existingDevice = Device::where('subscription_id', $subscription->id)
            ->where('hwid', $hwid)
            ->first();

        if ($existingDevice) {
            $existingDevice->updateActivity($ipAddress);
            return $existingDevice;
        }

        if (!$subscription->canAddDevice()) {
            Log::warning('Device limit reached', [
                'subscription_id' => $subscription->id,
                'hwid' => $hwid,
                'current_devices' => $subscription->devices()->count(),
                'max_devices' => $subscription->max_devices,
            ]);
            return null;
        }

        return Device::create([
            'subscription_id' => $subscription->id,
            'hwid' => $hwid,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'last_active_at' => now(),
        ]);
    }

    public function validateDevice(Subscription $subscription, string $hwid): bool
    {
        if (!$subscription->isActive()) {
            return false;
        }

        $device = Device::where('subscription_id', $subscription->id)
            ->where('hwid', $hwid)
            ->first();

        if ($device) {
            $device->updateActivity();
            return true;
        }

        if ($subscription->canAddDevice()) {
            return true;
        }

        return false;
    }

    public function getActiveDevices(Subscription $subscription): \Illuminate\Database\Eloquent\Collection
    {
        return $subscription->devices()->latest('last_active_at')->get();
    }

    public function removeDevice(Device $device): bool
    {
        return $device->delete();
    }

    public function extractHwidFromRequest(\Illuminate\Http\Request $request): ?string
    {
        $hwid = $request->input('hwid')
            ?? $request->input('device_id')
            ?? $request->header('X-Device-ID')
            ?? $request->header('X-HWID')
            ?? $request->header('X-Hwid')
            ?? $request->query('hwid')
            ?? $request->query('device_id');

        if ($hwid) {
            return $hwid;
        }

        $userAgent = $request->header('User-Agent', '');
        $ip = $request->ip();

        if ($userAgent && $ip) {
            return md5($userAgent . $ip);
        }

        return null;
    }
}
