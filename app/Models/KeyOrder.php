<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyOrder extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'status',
        'subscription_key_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionKey(): BelongsTo
    {
        return $this->belongsTo(SubscriptionKey::class);
    }
}
