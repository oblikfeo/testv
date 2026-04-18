<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleKey extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'key_order_id',
        'panel_index',
        'uuid',
        'email',
        'sub_id',
        'inbound_id',
        'total_bytes',
        'used_bytes',
        'expires_at',
        'activated_at',
        'is_sponsor',
        'secondary_panel_index',
        'secondary_uuid',
        'secondary_email',
        'secondary_sub_id',
        'secondary_inbound_id',
        'is_admin_bundle',
        'admin_primary_is_test',
        'bundle_endpoints',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'is_sponsor' => 'boolean',
            'is_admin_bundle' => 'boolean',
            'admin_primary_is_test' => 'boolean',
            'bundle_endpoints' => 'array',
            'total_bytes' => 'integer',
            'used_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function keyOrder(): BelongsTo
    {
        return $this->belongsTo(KeyOrder::class, 'key_order_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isTrafficExceeded(): bool
    {
        if ($this->total_bytes === 0) {
            return false;
        }

        return $this->used_bytes >= $this->total_bytes;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired() && ! $this->isTrafficExceeded();
    }

    public function getUsagePercent(): int
    {
        if ($this->total_bytes === 0) {
            return 0;
        }

        return min(100, (int) round($this->used_bytes / $this->total_bytes * 100));
    }
}
