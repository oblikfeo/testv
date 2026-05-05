<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialFeedback extends Model
{
    protected $table = 'trial_feedback';

    protected $fillable = [
        'user_id',
        'telegram_id',
        'telegram_username',
        'trigger',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
