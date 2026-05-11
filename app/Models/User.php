<?php

namespace App\Models;

use App\Support\Enums\SubscriptionPlan;
// use Illuminate\Contracts\Auth\MustVerifyEmail; // TODO: إعادة تفعيله قبل الإطلاق (Phase 13)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'timezone',
        'subscription_plan',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'subscription_plan' => SubscriptionPlan::class,
        ];
    }

    // ==================== Relations ====================

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // ==================== Helpers ====================

    public function currentPlan(): SubscriptionPlan
    {
        return $this->subscription_plan ?? SubscriptionPlan::Free;
    }

    public function isOnPlan(SubscriptionPlan $plan): bool
    {
        return $this->subscription_plan === $plan;
    }

    public function canCreateMoreProjects(): bool
    {
        $max = $this->currentPlan()->maxProjects();
        return $this->projects()->count() < $max;
    }
}
