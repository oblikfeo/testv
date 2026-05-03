<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    public const AUTHOR_USER = 'user';

    public const AUTHOR_ADMIN = 'admin';

    protected $fillable = [
        'ticket_id',
        'author_type',
        'author_user_id',
        'body',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->author_type === self::AUTHOR_ADMIN;
    }
}
