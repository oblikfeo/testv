<?php

namespace App\Http\Controllers;

use App\Models\SaleKey;
use App\Models\TrialDevice;
use App\Models\TrialKey;
use App\Services\SubscriptionFeedBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    protected const TRIAL_MAX_DEVICES = 1;

    public function __construct(
        protected SubscriptionFeedBuilder $feedBuilder
    ) {}

    public function show(string $subId, Request $request): Response
    {
        $saleKey = SaleKey::query()->where('sub_id', $subId)->first();
        if ($saleKey) {
            $data = $this->feedBuilder->buildForSale($saleKey);
            if (isset($data['error'])) {
                return response($data['error'], $data['code'] ?? 500);
            }

            return $this->respondHapp($request, $data['body'], $data['user_info'], $data['profile_title']);
        }

        $trialKey = TrialKey::query()->where('sub_id', $subId)->first();
        if (! $trialKey) {
            return response('Подписка не найдена', 404);
        }

        $deviceCheck = $this->checkAndRegisterTrialDevice($trialKey, $request);
        if ($deviceCheck !== null) {
            return $deviceCheck;
        }

        $data = $this->feedBuilder->buildForTrial($trialKey);
        if (isset($data['error'])) {
            return response($data['error'], $data['code'] ?? 500);
        }

        return $this->respondHapp($request, $data['body'], $data['user_info'], $data['profile_title']);
    }

    protected function checkAndRegisterTrialDevice(TrialKey $trialKey, Request $request): ?Response
    {
        if (! $trialKey->isActive()) {
            return null;
        }

        $hwid = $this->extractHwid($request);
        if (! $hwid) {
            return null;
        }

        $existingDevice = TrialDevice::where('trial_key_id', $trialKey->id)
            ->where('hwid', $hwid)
            ->first();

        if ($existingDevice) {
            $existingDevice->updateActivity($request->ip());
            return null;
        }

        $currentDevices = TrialDevice::where('trial_key_id', $trialKey->id)->count();
        if ($currentDevices >= self::TRIAL_MAX_DEVICES) {
            return response(
                "Лимит устройств для тестового периода исчерпан ({$currentDevices}/" . self::TRIAL_MAX_DEVICES . "). " .
                "Удалите устройство в личном кабинете: " . url('/cabinet/trial'),
                403
            );
        }

        TrialDevice::create([
            'trial_key_id' => $trialKey->id,
            'hwid' => $hwid,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'last_active_at' => now(),
        ]);

        return null;
    }

    protected function extractHwid(Request $request): ?string
    {
        $hwid = $request->header('X-Device-ID')
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

    protected function respondHapp(Request $request, string $body, string $userInfo, string $profileTitle): Response
    {
        $userAgent = $request->header('User-Agent', '');

        if ($this->isHappClient($userAgent)) {
            $routing = \App\Support\HappSubscriptionFormatter::happRoutingDeeplinkFromConfig();
            $bodyWithRouting = $routing ? ($routing."\n".$body) : $body;

            $resp = response($bodyWithRouting)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('subscription-userinfo', $userInfo)
                ->header('profile-title', 'base64:'.base64_encode($profileTitle))
                ->header('profile-update-interval', '1')
                ->header('support-url', config('app.url'))
                ->header('X-Device-Register-Url', url('/api/device/register'))
                ->header('X-Device-Validate-Url', url('/api/device/validate'));

            if ($routing) {
                $resp->header('routing', $routing);
            }

            return $resp;
        }

        return response($body)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('subscription-userinfo', $userInfo);
    }

    protected function isHappClient(string $userAgent): bool
    {
        $happClients = ['happ', 'hiddify', 'v2rayn', 'v2rayng', 'streisand', 'shadowrocket', 'quantumult', 'clash'];
        $userAgentLower = strtolower($userAgent);

        foreach ($happClients as $client) {
            if (str_contains($userAgentLower, $client)) {
                return true;
            }
        }

        return false;
    }
}
