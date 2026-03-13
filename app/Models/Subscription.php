<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan',
        'paddle_subscription_id',
        'paddle_price_id',
        'status',
        'renews_at',
        'ends_at',
    ];

    protected $casts = [
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paddleTransactions(): HasMany
    {
        return $this->hasMany(PaddleTransaction::class);
    }
}
