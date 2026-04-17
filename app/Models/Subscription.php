<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'max_devices',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function saleKey(): HasOne
    {
        return $this->hasOne(SaleKey::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function canAddDevice(): bool
    {
        return $this->devices()->count() < $this->max_devices;
    }

    public function getDevicesCountAttribute(): int
    {
        return $this->devices()->count();
    }

    public function getDaysLeftAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        return (int) now()->diffInDays($this->expires_at, false);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }
}
