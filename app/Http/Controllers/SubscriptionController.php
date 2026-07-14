<?php

namespace App\Http\Controllers;

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
            return response('Подписка не активна', 403);
        }

        $body = SharedVpnAccess::subscriptionBody();
        if ($body === '') {
            return response('Подключение не настроено', 500);
        }

        $routing = HappRouting::isVpnClient($request->header('User-Agent', ''))
            ? HappRouting::subscriptionLine()
            : null;

        if ($routing) {
            $decoded = base64_decode($body, true);
            $lines = is_string($decoded) && $decoded !== '' ? explode("\n", trim($decoded)) : [];
            if (! in_array($routing, $lines, true)) {
                array_unshift($lines, $routing);
            }
            $body = base64_encode(implode("\n", $lines));
        }

        $headers = [
            'Content-Type' => 'text/plain; charset=utf-8',
            'profile-update-interval' => '12',
            'profile-title' => SharedVpnAccess::PROFILE_TITLE,
        ];

        if ($routing) {
            $headers['routing'] = $routing;
        }

        if (HappRouting::isVpnClient($request->header('User-Agent', ''))) {
            if (HappRouting::subscriptionPinEnabled()) {
                $headers['subscription-pin'] = 'true';
            }

            $announce = HappRouting::announcementHeader();
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
