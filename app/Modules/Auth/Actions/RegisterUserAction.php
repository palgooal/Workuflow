<?php

namespace App\Modules\Auth\Actions;

use App\Http\Requests\Auth\RegisterRequest;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\Category;
use App\Models\User;
use App\Modules\Referral\Services\ReferralService;
use App\Support\Enums\SubscriptionPlan;
use App\Support\Enums\TransactionType;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegisterUserAction
{
    public function execute(RegisterRequest $request): User
    {
        // إنشاء المستخدم
        $user = User::create([
            'name'                    => $request->name,
            'email'                   => $request->email,
            'phone'                   => $request->phone, // E.164: +970599123456
            'password'                => $request->password,
            'currency'                => $request->currency,
            'timezone'                => $request->timezone,
            'subscription_plan'       => SubscriptionPlan::Free,
            'registration_ip'         => $request->ip(),
            'registration_user_agent' => $request->userAgent(),
        ]);

        // ── Referral Attribution ──────────────────────────────────────────
        // يُنفَّذ مباشرة بعد User::create() وقبل Auth::login() لأن:
        //   • attributeRegistration() تُحدَّث referred_by_affiliate_id في DB مباشرة
        //   • يجب تنفيذه قبل event(Registered) لضمان وجود الربط قبل أي Listener يقرأه
        //
        // مصدر البيانات (بالأولوية):
        //   1. Session  — إذا سجّل المستخدم مباشرة بعد زيارة /ref/{code}
        //   2. Cookie   — إذا تأخّر التسجيل (60 يوم من زيارة الرابط)
        //
        // الشروط: كلا المعرّفَين (affiliate_id + click_id) مطلوبان لضمان سلامة FK
        $affiliateId = $request->session()->get('referral_affiliate_id')
            ?? $request->cookie('ref_aff');

        $clickId = $request->session()->get('referral_click_id')
            ?? $request->cookie('ref_clk');

        if ($affiliateId && $clickId) {
            try {
                app(ReferralService::class)->attributeRegistration(
                    $user,
                    $affiliateId,
                    $clickId,
                );
            } catch (\Throwable $e) {
                // Attribution لا يجوز أن يكسر التسجيل — يُسجَّل فقط
                Log::error('Referral attribution failed during registration', [
                    'user_id'      => $user->id,
                    'affiliate_id' => $affiliateId,
                    'click_id'     => $clickId,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        // إطلاق حدث التسجيل (يُرسل بريد التحقق تلقائياً)
        event(new Registered($user));

        // إنشاء الفئات الافتراضية للمستخدم الجديد
        $this->createDefaultCategories($user);

        // إرسال بريد الترحيب عبر Queue (لا يُعيق التسجيل)
        SendWelcomeEmailJob::dispatch($user)->delay(now()->addSeconds(5));

        // تسجيل الدخول تلقائياً
        Auth::login($user);

        // ── CONVERSION-01 Pre-payment Grace (30 minutes) ─────────────────
        // المشكلة: billing.upgrade محمي بـ verified middleware، لكن المستخدم
        // لم يوثّق بريده بعد عند التسجيل — مما يُعيد توجيهه لصفحة التحقق
        // قبل أن يصل لصفحة الدفع.
        //
        // الحل: نمنح فترة سماح مؤقتة مدتها 30 دقيقة تسمح للمستخدم بالوصول
        // لصفحة الدفع وإتمام الشراء.
        //
        // ملاحظات مهمة:
        //   • grace_used_at لا يُضبط هنا — هذه ليست فترة السماح الرسمية (7 أيام).
        //   • الخطة تبقى Free حتى تأكيد الدفع في SubscriptionService::activatePlan().
        //   • بعد الدفع: activatePlan() يُرقّي grace_until لـ 7 أيام ويضبط grace_used_at.
        //   • إذا لم يتم الدفع خلال 30 دقيقة: grace_until تنتهي وverified middleware
        //     يُعيد تطبيق نفسه تلقائياً دون أي تدخل.
        //   • كتابة session('paid_intent') والـ redirect تتم في RegisteredUserController
        //     مباشرة بعد execute() — لتجنب race condition مع Auth::login() session migration.
        $planIntent = $request->input('plan_intent');
        if (in_array($planIntent, ['pro', 'business'], true)) {
            $user->update([
                'email_verification_grace_until' => now()->addMinutes(30),
                // grace_used_at intentionally null — reserved for post-payment 7-day grace
            ]);
        }

        return $user;
    }

    private function createDefaultCategories(User $user): void
    {
        $defaults = [
            // فئات الدخل
            ['name' => 'راتب',        'type' => TransactionType::Income,  'icon' => 'cash',       'color' => '#10b981'],
            ['name' => 'مبيعات',      'type' => TransactionType::Income,  'icon' => 'shopping-bag','color' => '#3b82f6'],
            ['name' => 'مشاريع',      'type' => TransactionType::Income,  'icon' => 'briefcase',  'color' => '#8b5cf6'],
            ['name' => 'استثمارات',   'type' => TransactionType::Income,  'icon' => 'trending-up', 'color' => '#f59e0b'],
            ['name' => 'هدايا',       'type' => TransactionType::Income,  'icon' => 'gift',        'color' => '#ec4899'],

            // فئات المصروفات
            ['name' => 'إيجار',       'type' => TransactionType::Expense, 'icon' => 'home',        'color' => '#ef4444'],
            ['name' => 'مواصلات',     'type' => TransactionType::Expense, 'icon' => 'truck',       'color' => '#f97316'],
            ['name' => 'طعام',        'type' => TransactionType::Expense, 'icon' => 'shopping-cart','color' => '#eab308'],
            ['name' => 'اشتراكات',    'type' => TransactionType::Expense, 'icon' => 'credit-card', 'color' => '#6366f1'],
            ['name' => 'تسويق',       'type' => TransactionType::Expense, 'icon' => 'megaphone',   'color' => '#14b8a6'],
            ['name' => 'فواتير',      'type' => TransactionType::Expense, 'icon' => 'document',    'color' => '#64748b'],
            ['name' => 'أخرى',        'type' => TransactionType::Expense, 'icon' => 'dots-horizontal','color' => '#94a3b8'],
        ];

        foreach ($defaults as $category) {
            Category::withoutGlobalScopes()->create([
                'user_id'    => $user->id,
                'name'       => $category['name'],
                'type'       => $category['type'],
                'icon'       => $category['icon'],
                'color'      => $category['color'],
                'is_default' => true,
            ]);
        }
    }
}
