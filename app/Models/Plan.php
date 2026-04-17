<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'devices',
        'days',
        'price',
        'discount',
        'is_popular',
        'is_active',
        'sort_order',
        'traffic_gb',
    ];

    protected function casts(): array
    {
        return [
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(KeyOrder::class);
    }

    public function getOriginalPriceAttribute(): int
    {
        if ($this->discount > 0) {
            return (int) round($this->price / (1 - $this->discount / 100));
        }
        return $this->price;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, '', ' ') . ' ₽';
    }

    public function getPeriodLabelAttribute(): string
    {
        return match ($this->days) {
            30 => '30 дней',
            90 => '90 дней',
            180 => '180 дней',
            365 => '1 год',
            default => $this->days . ' дней',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('devices')->orderBy('days');
    }
}
