<?php

namespace App\Http\Controllers;

use App\Models\SaleKey;
use App\Models\TrialKey;
use App\Support\SharedVpnAccess;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy URL /sub/{subId} — для старых закладок Happ.
 * Отдаёт ту же статическую hy2-ссылку, если у владельца ключа ещё есть доступ.
 */
class SubscriptionController extends Controller
{
    public function show(string $subId, Request $request): Response
    {
        $user = $this->resolveUserBySubId($subId);
        if (! $user) {
            return response('Подписка не найдена', 404);
        }

        if (! SharedVpnAccess::userHasAccess($user)) {
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

    protected function resolveUserBySubId(string $subId): ?\App\Models\User
    {
        $trial = TrialKey::query()->where('sub_id', $subId)->with('user')->first();
        if ($trial?->user) {
            return $trial->user;
        }

        $saleKey = SaleKey::query()->where('sub_id', $subId)->with('user')->first();
        if ($saleKey?->user) {
            return $saleKey->user;
        }

        return null;
    }
}
