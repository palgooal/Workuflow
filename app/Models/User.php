<?php

namespace App\Models;

use App\Support\Enums\SubscriptionPlan;
use App\Support\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'phone',
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
        'email_verification_grace_until',    // CONVERSION-01 Phase 2
        'email_verification_grace_used_at',  // CONVERSION-01 Phase 2
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'                  => 'datetime',
            'password'                           => 'hashed',
            'subscription_plan'                  => SubscriptionPlan::class,
            'status'                             => UserStatus::class,
            'onboarding_dismissed_at'            => 'datetime',
            'last_login_at'                      => 'datetime',
            'email_verification_grace_until'     => 'datetime', // CONVERSION-01 Phase 2
            'email_verification_grace_used_at'   => 'datetime', // CONVERSION-01 Phase 2
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

    // ==================== Referral Relations ====================

    /** المسوّق المرتبط بهذا الحساب (إن كان المستخدم مسوّقاً) */
    public function affiliate(): HasOne
    {
        return $this->hasOne(\App\Modules\Referral\Models\Affiliate::class);
    }

    /** المسوّق الذي أحال هذا المستخدم */
    public function referredByAffiliate(): BelongsTo
    {
        return $this->belongsTo(
            \App\Modules\Referral\Models\Affiliate::class,
            'referred_by_affiliate_id'
        );
    }

    /** سجل النقرة الذي أفضى لتسجيل هذا المستخدم */
    public function referralClick(): BelongsTo
    {
        return $this->belongsTo(
            \App\Modules\Referral\Models\ReferralClick::class,
            'referral_click_id'
        );
    }

    // ==================== Email Verification Grace (CONVERSION-01 Phase 2) ====================

    /**
     * Override MustVerifyEmail's hasVerifiedEmail().
     *
     * يعيد true إذا:
     *   (أ) البريد موثَّق بشكل طبيعي (email_verified_at ≠ null)
     *   (ب) أو المستخدم ضمن فترة السماح المدفوعة (grace_until في المستقبل)
     *
     * تأثيره: middleware 'verified' (EnsureEmailIsVerified) يستدعي هذه الدالة مباشرة،
     * مما يعني أن المستخدم المدفوع غير الموثَّق يتجاوز الحاجز تلقائياً خلال فترة السماح.
     * بعد انتهاء فترة السماح يعود السلوك الطبيعي — user يُحال لصفحة التحقق.
     */
    public function hasVerifiedEmail(): bool
    {
        // (أ) الحالة الطبيعية — البريد موثَّق
        if ($this->email_verified_at !== null) {
            return true;
        }

        // (ب) فترة السماح المدفوعة — لا تزال سارية
        if ($this->email_verification_grace_until !== null
            && $this->email_verification_grace_until->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * هل المستخدم حالياً ضمن فترة السماح المدفوعة النشطة؟
     * (غير موثَّق + فترة السماح لم تنتهِ بعد)
     */
    public function isInEmailVerificationGrace(): bool
    {
        return $this->email_verified_at === null
            && $this->email_verification_grace_until !== null
            && $this->email_verification_grace_until->isFuture();
    }

    /**
     * هل استُخدمت فترة السماح من قبل (سواء انتهت أو لا)؟
     * يُستخدم لمنع منح فترة سماح جديدة عند تجديد الاشتراك.
     */
    public function hasUsedEmailVerificationGrace(): bool
    {
        return $this->email_verification_grace_used_at !== null;
    }

    /**
     * عدد الأيام المتبقية في فترة السماح (صحيح، كحد أدنى 1).
     * يُستخدم في بانر التحذير.
     */
    public function graceDaysRemaining(): int
    {
        if (! $this->isInEmailVerificationGrace()) {
            return 0;
        }

        return max(1, (int) ceil(now()->floatDiffInDays($this->email_verification_grace_until)));
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

    /** استخدام قالب البريد المخصص لتحقق البريد الإلكتروني */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\CustomVerifyEmailNotification());
    }

    // ==================== Filament Admin ====================

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super_admin') && $this->isActive();
    }
}
