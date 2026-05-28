<?php

namespace App\Services;

use App\Models\TrialKey;
use App\Models\User;
use App\Support\SharedVpnAccess;

class TrialKeyService
{
    public function createTrialKey(User $user): TrialKey
    {
        if (! $user->canUseTrial()) {
            throw new \Exception('Пользователь не может получить тестовый период');
        }

        $durationHours = (int) config('vpn.trial.duration_hours', 3);
        $softQuotaGb = (int) config('vpn.trial.soft_quota_gb', 0);

        return $this->issueTrialKey($user, $durationHours, $softQuotaGb);
    }

    public function createTrialKeyForAdmin(User $user, int $durationHours, int $softQuotaGb): TrialKey
    {
        return $this->issueTrialKey(
            $user,
            max(1, $durationHours),
            max(0, $softQuotaGb),
            replaceExisting: true
        );
    }

    public function revokeTrialKey(TrialKey $trialKey): void
    {
        $userId = $trialKey->user_id;
        $trialKey->delete();

        $user = User::query()->find($userId);
        if ($user && ! TrialKey::query()->where('user_id', $userId)->exists()) {
            $user->update(['trial_used' => false]);
        }
    }

    protected function issueTrialKey(User $user, int $durationHours, int $softQuotaGb, bool $replaceExisting = false): TrialKey
    {
        if ($replaceExisting) {
            $existing = TrialKey::query()->where('user_id', $user->id)->first();
            if ($existing) {
                $this->revokeTrialKey($existing);
            }
        }

        $subId = SharedVpnAccess::ensureVpnSubId($user);

        $trialKey = TrialKey::create([
            'user_id' => $user->id,
            'sub_id' => $subId,
            'total_bytes' => $softQuotaGb > 0 ? $softQuotaGb * 1024 * 1024 * 1024 : 0,
            'used_bytes' => 0,
            'expires_at' => now()->addHours($durationHours),
            'activated_at' => now(),
        ]);

        $user->update(['trial_used' => true]);

        return $trialKey;
    }
}
