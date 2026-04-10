<?php

namespace App\Models;

use App\Enums\SubscriptionKeyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionKey extends Model
{
    protected $fillable = [
        'pair_id',
        'status',
        'connection_url',
        'panel_client_id',
        'panel_raw',
        'user_id',
        'issued_at',
        'activated_at',
        'expires_at',
        'created_in_panel_at',
    ];

    protected function casts(): array
    {
        return [
            'panel_raw' => 'array',
            'issued_at' => 'datetime',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_in_panel_at' => 'datetime',
        ];
    }

    public function pair(): BelongsTo
    {
        return $this->belongsTo(Pair::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === SubscriptionKeyStatus::Available->value;
    }
}
