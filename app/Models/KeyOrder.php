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
        'sale_key_id',
        'plan_id',
        'payment_id',
        'payment_status',
        'amount',
        'payment_method',
        'paid_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function saleKey(): BelongsTo
    {
        return $this->belongsTo(SaleKey::class);
    }
}
