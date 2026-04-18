<?php

namespace App\Http\Controllers;

use App\Models\SaleKey;
use App\Models\TrialKey;
use App\Services\SubscriptionFeedBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
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

        $data = $this->feedBuilder->buildForTrial($trialKey);
        if (isset($data['error'])) {
            return response($data['error'], $data['code'] ?? 500);
        }

        return $this->respondHapp($request, $data['body'], $data['user_info'], $data['profile_title']);
    }

    protected function respondHapp(Request $request, string $body, string $userInfo, string $profileTitle): Response
    {
        $userAgent = $request->header('User-Agent', '');

        if ($this->isHappClient($userAgent)) {
            return response($body)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('subscription-userinfo', $userInfo)
                ->header('profile-title', 'base64:'.base64_encode($profileTitle))
                ->header('profile-update-interval', '1')
                ->header('support-url', config('app.url'))
                ->header('X-Device-Register-Url', url('/api/device/register'))
                ->header('X-Device-Validate-Url', url('/api/device/validate'));
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
