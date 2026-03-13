<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;

    public const PLAN_STARTER = 'starter';

    public const PLAN_PRO = 'pro';

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan',
        'is_admin',
        'plan_renews_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_renews_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function paddleTransactions(): HasMany
    {
        return $this->hasMany(PaddleTransaction::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->whereIn('status', ['active', 'trialing']);
    }

    public function planRecord(): HasOne
    {
        return $this->hasOne(Plan::class, 'internal_code', 'plan');
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function projectLimit(): ?int
    {
        return $this->currentPlan() === self::PLAN_PRO ? null : 5;
    }

    public function canTrackTime(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function currentPlan(): string
    {
        if (! $this->hasActiveSubscription()) {
            return self::PLAN_STARTER;
        }

        $subscriptionPlan = $this->relationLoaded('activeSubscription')
            ? $this->activeSubscription?->plan
            : $this->activeSubscription()->value('plan');

        return $subscriptionPlan ?? self::PLAN_STARTER;
    }
}
