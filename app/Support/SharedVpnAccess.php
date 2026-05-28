<?php

namespace App\Support;

use App\Models\TrialKey;
use App\Models\User;
use Illuminate\Support\Str;

class SharedVpnAccess
{
    /**
     * @return list<string>
     */
    public static function nodeUris(): array
    {
        $uris = [];

        foreach (['shared_hy2_uri', 'shared_vless_uri'] as $key) {
            $uri = trim((string) config("vpn.{$key}", ''));
            if ($uri !== '') {
                $uris[] = $uri;
            }
        }

        return $uris;
    }

    public static function subscriptionBody(): string
    {
        $uris = self::nodeUris();
        if ($uris === []) {
            return '';
        }

        return base64_encode(implode("\n", $uris));
    }

    public static function subscriptionPublicId(User $user): string
    {
        return self::ensureVpnSubId($user);
    }

    public static function subscriptionUrl(User $user): string
    {
        return route('subscription.show', [
            'subId' => self::subscriptionPublicId($user),
        ], absolute: true);
    }

    public static function ensureVpnSubId(User $user): string
    {
        if ($user->vpn_sub_id) {
            return $user->vpn_sub_id;
        }

        do {
            $id = Str::random(16);
        } while (
            User::query()->where('vpn_sub_id', $id)->exists()
            || TrialKey::query()->where('sub_id', $id)->exists()
        );

        $user->forceFill(['vpn_sub_id' => $id])->save();

        return $id;
    }

    public static function userHasAccess(User $user): bool
    {
        if ($user->activeSubscriptions()->exists()) {
            return true;
        }

        $trial = $user->trialKey;

        return $trial !== null && $trial->isActive();
    }

    public static function connectionUriForUser(User $user): ?string
    {
        if (! self::userHasAccess($user)) {
            return null;
        }

        if (self::nodeUris() === []) {
            return null;
        }

        return self::subscriptionUrl($user);
    }

    /**
     * @deprecated Используйте nodeUris() или subscriptionUrl()
     */
    public static function connectionUri(): string
    {
        $uris = self::nodeUris();

        return $uris[0] ?? '';
    }

    public static function trialIsActive(?TrialKey $trialKey): bool
    {
        return $trialKey !== null && $trialKey->isActive();
    }

    public static function activeTrialKey(User $user): ?TrialKey
    {
        $trial = $user->trialKey;

        return self::trialIsActive($trial) ? $trial : null;
    }

    public static function accessExpiresAt(User $user): ?\Illuminate\Support\Carbon
    {
        $latest = null;

        foreach ($user->activeSubscriptions()->get() as $subscription) {
            if ($subscription->expires_at && ($latest === null || $subscription->expires_at->gt($latest))) {
                $latest = $subscription->expires_at;
            }
        }

        $trial = self::activeTrialKey($user);
        if ($trial?->expires_at) {
            if ($latest === null || $trial->expires_at->gt($latest)) {
                $latest = $trial->expires_at;
            }
        }

        return $latest;
    }

    public static function resolveUserBySubId(string $subId): ?User
    {
        $user = User::query()->where('vpn_sub_id', $subId)->first();
        if ($user) {
            return $user;
        }

        $trialKey = TrialKey::query()->where('sub_id', $subId)->with('user')->first();

        return $trialKey?->user;
    }
}
