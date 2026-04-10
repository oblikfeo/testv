<?php

namespace App\Models;

use App\Enums\SubscriptionKeyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pair extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'panel_base_url',
        'panel_username',
        'panel_password',
        'inbound_id',
        'remark_prefix',
        'batch_size',
        'refill_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'inbound_id' => 'integer',
            'batch_size' => 'integer',
            'refill_threshold' => 'integer',
            'is_active' => 'boolean',
            'panel_username' => 'encrypted',
            'panel_password' => 'encrypted',
        ];
    }

    public function subscriptionKeys(): HasMany
    {
        return $this->hasMany(SubscriptionKey::class);
    }

    public function availableKeys(): HasMany
    {
        return $this->subscriptionKeys()->where('status', SubscriptionKeyStatus::Available->value);
    }
}
