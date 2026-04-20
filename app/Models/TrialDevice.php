<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialDevice extends Model
{
    protected $fillable = [
        'trial_key_id',
        'hwid',
        'name',
        'user_agent',
        'ip_address',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
        ];
    }

    public function trialKey(): BelongsTo
    {
        return $this->belongsTo(TrialKey::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        if ($this->user_agent) {
            return $this->parseDeviceName($this->user_agent);
        }

        return 'Устройство #' . $this->id;
    }

    protected function parseDeviceName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows PC';
        }
        if (str_contains($userAgent, 'Mac')) {
            return 'MacOS';
        }
        if (str_contains($userAgent, 'iPhone')) {
            return 'iPhone';
        }
        if (str_contains($userAgent, 'iPad')) {
            return 'iPad';
        }
        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Неизвестное устройство';
    }

    public function updateActivity(?string $ipAddress = null): void
    {
        $this->update([
            'last_active_at' => now(),
            'ip_address' => $ipAddress ?? $this->ip_address,
        ]);
    }
}
