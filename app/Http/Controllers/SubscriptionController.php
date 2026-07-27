<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\HappRouting;
use App\Support\SharedVpnAccess;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    /**
     * GET /sub/{subId} — подписка для Happ / v2RayTun (base64, несколько узлов).
     */
    public function show(string $subId, Request $request): Response
    {
        $user = SharedVpnAccess::resolveUserBySubId($subId);
        if (! $user) {
            return response('Подписка не найдена', 404);
        }

        if (! SharedVpnAccess::userHasAccess($user)) {
            return $this->expiredResponse($user, $request);
        }

        $body = SharedVpnAccess::subscriptionBody();
        if ($body === '') {
            return response('Подключение не настроено', 500);
        }

        $isVpnClient = HappRouting::isVpnClient($request->header('User-Agent', ''));

        $routing = $isVpnClient ? HappRouting::subscriptionLine() : null;

        if ($routing) {
            $decoded = base64_decode($body, true);
            $lines = is_string($decoded) && $decoded !== '' ? explode("\n", trim($decoded)) : [];
            if (! in_array($routing, $lines, true)) {
                array_unshift($lines, $routing);
            }
            $body = base64_encode(implode("\n", $lines));
        }

        return $this->subscriptionResponse($user, $body, $isVpnClient, $routing, active: true);
    }

    private function expiredResponse(User $user, Request $request): Response
    {
        $isVpnClient = HappRouting::isVpnClient($request->header('User-Agent', ''));

        return $this->subscriptionResponse(
            $user,
            SharedVpnAccess::expiredSubscriptionBody(),
            $isVpnClient,
            $isVpnClient ? HappRouting::ROUTING_OFF : null,
            active: false,
        );
    }

    private function subscriptionResponse(
        User $user,
        string $body,
        bool $isVpnClient,
        ?string $routing,
        bool $active,
    ): Response {
        $headers = [
            'Content-Type' => 'text/plain; charset=utf-8',
            'profile-update-interval' => '12',
            'profile-title' => $active ? SharedVpnAccess::PROFILE_TITLE : SharedVpnAccess::EXPIRED_PROFILE_TITLE,
        ];

        if ($routing) {
            $headers['routing'] = $routing;
        }

        if ($isVpnClient) {
            if ($active && HappRouting::subscriptionPinEnabled()) {
                $headers['subscription-pin'] = 'true';
            }

            $announce = $active
                ? HappRouting::announcementHeader()
                : HappRouting::expiredAnnouncementHeader();

            if ($announce !== null) {
                $headers['announce'] = $announce;
            }
        }

        $expiresAt = SharedVpnAccess::accessExpiresAt($user);
        if ($expiresAt) {
            $headers['subscription-userinfo'] = sprintf(
                'upload=0; download=0; total=0; expire=%d',
                $expiresAt->timestamp
            );
        }

        $supportUrl = config('app.telegram_support_url');
        if (is_string($supportUrl) && $supportUrl !== '') {
            $headers['profile-web-page-url'] = $supportUrl;
        }

        return response($body, 200, $headers);
    }
}
