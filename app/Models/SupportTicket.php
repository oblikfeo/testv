<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING_USER = 'pending_user';

    public const STATUS_CLOSED = 'closed';

    public const CATEGORIES = [
        'payment' => 'Оплата и возврат',
        'connection' => 'Подключение / VPN не работает',
        'speed' => 'Скорость / стабильность',
        'devices' => 'Устройства / лимиты',
        'account' => 'Аккаунт / вход',
        'other' => 'Другое',
    ];

    protected $fillable = [
        'user_id',
        'subject',
        'category',
        'status',
        'last_message_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function isOpen(): bool
    {
        return $this->status !== self::STATUS_CLOSED;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Ждёт ответа поддержки',
            self::STATUS_PENDING_USER => 'Ждёт вашего ответа',
            self::STATUS_CLOSED => 'Закрыт',
            default => $this->status,
        };
    }
}
