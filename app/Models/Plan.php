<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_code',
        'paddle_product_id',
        'paddle_price_id',
        'name',
        'currency',
        'amount',
        'billing_interval',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'amount' => 'decimal:2',
    ];
}
