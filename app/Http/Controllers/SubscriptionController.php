<?php

namespace App\Http\Controllers;

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

        $headers = [
            'Content-Type' => 'text/plain; charset=utf-8',
            'profile-update-interval' => '12',
            'profile-title' => SharedVpnAccess::PROFILE_TITLE,
        ];

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
