<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrialFeedbackRequest extends Model
{
    protected $fillable = [
        'user_id',
        'trial_key_id',
        'trigger',
        'email_sent_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'email_sent_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trialKey(): BelongsTo
    {
        return $this->belongsTo(TrialKey::class);
    }
}
