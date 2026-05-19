<?php

namespace App\Services;

use App\Models\TrialKey;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Триальный ключ: в модели «общая подписка для всех» персональный VLESS-клиент в 3x-ui больше
 * не создаётся. Запись TrialKey хранит только sub_id, срок и soft-квоту трафика для отображения
 * в subscription-userinfo. По истечении срока /sub/{sub_id} возвращает 403.
 */
class TrialKeyService
{
    public function createTrialKey(User $user): TrialKey
    {
        if (! $user->canUseTrial()) {
            throw new \Exception('Пользователь не может получить тестовый ключ');
        }

        $durationHours = (int) config('admin.trial.duration_hours', 3);
        $softQuotaGb = (int) config('admin.trial.soft_quota_gb', 5);

        return $this->issueTrialKey($user, $durationHours, $softQuotaGb);
    }

    /**
     * Выдача триала из админки: можно переопределить срок/квоту и перевыпустить ключ.
     */
    public function createTrialKeyForAdmin(User $user, int $durationHours, int $softQuotaGb): TrialKey
    {
        $durationHours = max(1, $durationHours);
        $softQuotaGb = max(0, $softQuotaGb);

        return $this->issueTrialKey($user, $durationHours, $softQuotaGb, replaceExisting: true);
    }

    public function revokeTrialKey(TrialKey $trialKey): void
    {
        $userId = $trialKey->user_id;
        $trialKey->devices()->delete();
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

        $email = 'trial-'.$user->id.'-'.time();
        $subId = Str::random(16);
        $sharedUuid = (string) config('admin.shared.vless_uuid', '');

        $trialKey = TrialKey::create([
            'user_id' => $user->id,
            'email' => $email,
            'uuid' => $sharedUuid !== '' ? $sharedUuid : Str::uuid()->toString(),
            'sub_id' => $subId,
            'panel_url' => null,
            'inbound_id' => 0,
            'total_bytes' => $softQuotaGb > 0 ? $softQuotaGb * 1024 * 1024 * 1024 : 0,
            'used_bytes' => 0,
            'expires_at' => now()->addHours($durationHours),
            'activated_at' => now(),
        ]);

        $user->update(['trial_used' => true]);

        return $trialKey;
    }

    /**
     * Совместимость с прежним API. В новой модели UUID общий для всех — трафик по нему
     * на стороне 3x-ui не делится по пользователям, soft-квота показывается как есть.
     */
    public function syncTrafficFromPanel(TrialKey $trialKey): void
    {
        // no-op: общий UUID, точный учёт трафика на пользователя невозможен.
    }

    /**
     * @deprecated Возвращался inbound панели 3x-ui для построения VLESS-линка. В новой модели
     * фид строится статически из config('admin.endpoints') и не зависит от inbound панели.
     */
    public function getInboundSettings(TrialKey $trialKey): ?array
    {
        return null;
    }
}
