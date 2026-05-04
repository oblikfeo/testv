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
        'telegram_first_name',
        'telegram_last_name',
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

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
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

    /**
     * Имя из Telegram: «Имя Фамилия». Возвращает null, если first_name/last_name не сохранены
     * (старые записи до миграции telegram_names или юзеры без TG).
     */
    public function telegramFullName(): ?string
    {
        $full = trim(((string) $this->telegram_first_name).' '.((string) $this->telegram_last_name));

        return $full !== '' ? $full : null;
    }

    /**
     * Подпись для админки: @username → «Имя Фамилия» → TG #id → null.
     * Используется в `admin/test-keys.blade.php` (вкладки тестовых и оплаченных ключей).
     */
    public function telegramDisplayLabel(): ?string
    {
        if ($this->telegram_username) {
            return '@'.$this->telegram_username;
        }

        $full = $this->telegramFullName();
        if ($full !== null) {
            return $full;
        }

        if ($this->telegram_id !== null) {
            return 'TG #'.$this->telegram_id;
        }

        return null;
    }

    /**
     * Кликабельная ссылка на профиль в Telegram. Если есть @username — стандартная https,
     * иначе deeplink `tg://user?id=…` (открывает чат в установленном клиенте).
     */
    public function telegramDeeplink(): ?string
    {
        if ($this->telegram_username) {
            return 'https://t.me/'.ltrim($this->telegram_username, '@');
        }

        if ($this->telegram_id !== null) {
            return 'tg://user?id='.$this->telegram_id;
        }

        return null;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }
}
