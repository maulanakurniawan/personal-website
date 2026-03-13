<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaddleTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'paddle_transaction_id',
        'paddle_subscription_id',
        'paddle_adjustment_id',
        'event_type',
        'status',
        'currency',
        'amount',
        'refund_amount',
        'adjustment_action',
        'adjustment_status',
        'occurred_at',
        'details',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'occurred_at' => 'datetime',
        'details' => 'array',
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
