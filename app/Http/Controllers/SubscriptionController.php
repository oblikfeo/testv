<?php

namespace App\Http\Controllers;

use App\Models\TrialKey;
use App\Models\User;
use App\Support\SharedVpnAccess;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy /sub/{subId} — только для старых ссылок с trial_keys.sub_id.
 */
class SubscriptionController extends Controller
{
    public function show(string $subId, Request $request): Response
    {
        $trialKey = TrialKey::query()->where('sub_id', $subId)->with('user')->first();
        if (! $trialKey?->user) {
            return response('Подписка не найдена', 404);
        }

        if (! SharedVpnAccess::userHasAccess($trialKey->user)) {
            return response('Подписка не активна', 403);
        }

        $uri = SharedVpnAccess::connectionUri();
        if ($uri === '') {
            return response('Подключение не настроено', 500);
        }

        return response($uri, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
