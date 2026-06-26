<?php

namespace App\Models;

use App\Support\Enums\SubscriptionPlan;
use App\Support\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'timezone',
        'target_margin_pct',
        'subscription_plan',
        'status',
        'onboarding_dismissed_at',
        'payment_customer_id',  // يُملأ عند ربط مزود الدفع
        'registration_ip',
        'registration_user_agent',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'subscription_plan'          => SubscriptionPlan::class,
            'status'                     => UserStatus::class,
            'onboarding_dismissed_at'    => 'datetime',
            'last_login_at'              => 'datetime',
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

    public function isActive(): bool
    {
        return ($this->status ?? UserStatus::Active) === UserStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    // ==================== Notifications ====================

    /** استخدام قالب البريد المخصص لإعادة تعيين كلمة المرور */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
    }

    // ==================== Filament Admin ====================

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin') && $this->isActive();
    }
}
