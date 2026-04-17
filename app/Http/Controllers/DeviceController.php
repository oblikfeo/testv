<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function destroy(Request $request, Device $device)
    {
        $user = $request->user();
        
        $subscription = $device->subscription;
        
        if ($subscription->user_id !== $user->id) {
            abort(403);
        }

        $device->delete();

        return back()->with('success', 'Устройство успешно удалено');
    }
}
