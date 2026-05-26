<?php

namespace App\Support;

use App\Models\TrialKey;
use App\Models\User;

class SharedVpnAccess
{
    public static function connectionUri(): string
    {
        return (string) config('vpn.shared_hy2_uri', '');
    }

    public static function userHasAccess(User $user): bool
    {
        if ($user->activeSubscriptions()->exists()) {
            return true;
        }

        $trial = $user->trialKey;
        if (! $trial) {
            return false;
        }

        return $trial->isActive();
    }

    public static function connectionUriForUser(User $user): ?string
    {
        if (! self::userHasAccess($user)) {
            return null;
        }

        $uri = self::connectionUri();

        return $uri !== '' ? $uri : null;
    }

    public static function trialIsActive(?TrialKey $trialKey): bool
    {
        return $trialKey !== null && $trialKey->isActive();
    }
}
