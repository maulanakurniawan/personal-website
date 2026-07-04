<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ValidationLead extends Model
{
    use SoftDeletes;

    public const STATUSES = ['new', 'reviewed', 'invited', 'rejected', 'spam'];

    protected $fillable = [
        'product_key', 'product_name', 'source_url', 'email', 'locale', 'target_category',
        'price_interest', 'notes', 'price_seen_currency', 'price_seen_amount', 'utm_source',
        'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'ip_hash', 'user_agent',
        'status', 'submission_count', 'last_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'price_seen_amount' => 'decimal:2',
            'last_submitted_at' => 'datetime',
        ];
    }
}
