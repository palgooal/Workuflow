<?php

namespace App\Modules\Auth\Actions;

use App\Http\Requests\Auth\RegisterRequest;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\Category;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use App\Support\Enums\TransactionType;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    public function execute(RegisterRequest $request): User
    {
        // إنشاء المستخدم
        $user = User::create([
            'name'                    => $request->name,
            'email'                   => $request->email,
            'password'                => $request->password,
            'currency'                => $request->currency,
            'timezone'                => $request->timezone,
            'subscription_plan'       => SubscriptionPlan::Free,
            'registration_ip'         => $request->ip(),
            'registration_user_agent' => $request->userAgent(),
        ]);

        // إطلاق حدث التسجيل (يُرسل بريد التحقق تلقائياً)
        event(new Registered($user));

        // إنشاء الفئات الافتراضية للمستخدم الجديد
        $this->createDefaultCategories($user);

        // إرسال بريد الترحيب عبر Queue (لا يُعيق التسجيل)
        SendWelcomeEmailJob::dispatch($user)->delay(now()->addSeconds(5));

        // تسجيل الدخول تلقائياً
        Auth::login($user);

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
