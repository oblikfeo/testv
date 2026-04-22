<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'trial_used',
        'telegram_id',
        'telegram_username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subscriptionKeys(): HasMany
    {
        return $this->hasMany(SubscriptionKey::class);
    }

    public function keyOrders(): HasMany
    {
        return $this->hasMany(KeyOrder::class);
    }

    public function trialKey()
    {
        return $this->hasOne(TrialKey::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at');
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->orderByDesc('expires_at');
    }

    /**
     * @return HasMany<SaleKey, User>
     */
    public function saleKeys(): HasMany
    {
        return $this->hasMany(SaleKey::class);
    }

    public function canUseTrial(): bool
    {
        if ($this->trial_used) {
            return false;
        }

        // Web-пользователь: обязателен подтверждённый email.
        // Бот-пользователь (связан только через telegram_id): email не требуется.
        return $this->hasVerifiedEmail() || $this->isBotOnly();
    }

    public function isBotOnly(): bool
    {
        return $this->telegram_id !== null
            && is_string($this->email)
            && str_starts_with($this->email, 'tg-')
            && str_ends_with($this->email, '@bot.avavpn.ru');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }
}
