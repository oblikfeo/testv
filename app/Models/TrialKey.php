<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialKey extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'uuid',
        'sub_id',
        'panel_url',
        'inbound_id',
        'total_bytes',
        'used_bytes',
        'expires_at',
        'activated_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'total_bytes' => 'integer',
            'used_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isTrafficExceeded(): bool
    {
        return $this->used_bytes >= $this->total_bytes;
    }

    public function isActive(): bool
    {
        return !$this->isExpired() && !$this->isTrafficExceeded();
    }

    public function getRemainingGb(): float
    {
        $remaining = $this->total_bytes - $this->used_bytes;
        return round(max(0, $remaining) / 1024 / 1024 / 1024, 2);
    }

    public function getUsedGb(): float
    {
        return round($this->used_bytes / 1024 / 1024 / 1024, 2);
    }

    public function getTotalGb(): float
    {
        return round($this->total_bytes / 1024 / 1024 / 1024, 0);
    }

    public function getUsagePercent(): int
    {
        if ($this->total_bytes === 0) return 0;
        return min(100, (int) round($this->used_bytes / $this->total_bytes * 100));
    }
}
