<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'client_id', 'name', 'notes', 'hourly_rate', 'rounding_enabled', 'rounding_unit_minutes', 'locked_at', 'last_used_at'];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'rounding_enabled' => 'boolean',
        'rounding_unit_minutes' => 'integer',
        'locked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
