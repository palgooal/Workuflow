<?php

namespace App\Services;

use App\Models\User;

class OnboardingService
{
    /**
     * خطوات الـ Onboarding بالترتيب
     */
    private array $steps = [
        [
            'key'         => 'create_project',
            'title'       => 'أنشئ مشروعك الأول',
            'description' => 'ابدأ بإنشاء مشروع لتنظيم معاملاتك المالية',
            'icon'        => '📁',
            'url_name'    => 'projects.create',
            'url_label'   => 'إنشاء مشروع',
        ],
        [
            'key'         => 'add_client',
            'title'       => 'أضف أول عميل',
            'description' => 'سجّل بيانات عميلك لإدارة علاقاتك التجارية',
            'icon'        => '👥',
            'url_name'    => 'clients.create',
            'url_label'   => 'إضافة عميل',
        ],
        [
            'key'         => 'create_invoice',
            'title'       => 'أنشئ أول فاتورة',
            'description' => 'أرسل فاتورة احترافية لعميلك بخطوات بسيطة',
            'icon'        => '🧾',
            'url_name'    => 'invoices.create',
            'url_label'   => 'إنشاء فاتورة',
        ],
        [
            'key'         => 'add_transaction',
            'title'       => 'سجّل أول معاملة',
            'description' => 'أضف دخلاً أو مصروفاً لتبدأ بتتبع ماليتك',
            'icon'        => '💸',
            'url_name'    => 'transactions.create',
            'url_label'   => 'إضافة معاملة',
        ],
        [
            'key'         => 'view_reports',
            'title'       => 'استعرض تقاريرك',
            'description' => 'شاهد تحليلاً شاملاً لوضعك المالي',
            'icon'        => '📊',
            'url_name'    => 'reports.index',
            'url_label'   => 'عرض التقارير',
        ],
    ];

    /**
     * هل يجب إظهار الـ Onboarding لهذا المستخدم؟
     */
    public function shouldShow(User $user): bool
    {
        // أُغلق يدوياً
        if ($user->onboarding_dismissed_at !== null) {
            return false;
        }

        // كمّلها كلها — لا داعي للإظهار
        return $this->getCompletedCount($user) < count($this->steps);
    }

    /**
     * إرجاع الخطوات مع حالة الإكمال لكل منها
     */
    public function getSteps(User $user): array
    {
        $completed = $this->getCompletedKeys($user);

        return array_map(function (array $step) use ($completed) {
            return array_merge($step, [
                'completed' => in_array($step['key'], $completed),
            ]);
        }, $this->steps);
    }

    /**
     * نسبة الإكمال (0–100)
     */
    public function getProgressPercentage(User $user): int
    {
        $total = count($this->steps);
        if ($total === 0) return 100;

        return (int) round(($this->getCompletedCount($user) / $total) * 100);
    }

    public function getCompletedCount(User $user): int
    {
        return count($this->getCompletedKeys($user));
    }

    public function getTotalCount(): int
    {
        return count($this->steps);
    }

    // ─── Private ────────────────────────────────────────────

    private function getCompletedKeys(User $user): array
    {
        $completed = [];

        if ($user->projects()->exists()) {
            $completed[] = 'create_project';
        }

        // العملاء — جدول clients في CRM
        if (\Illuminate\Support\Facades\DB::table('clients')
            ->where('user_id', $user->id)->exists()) {
            $completed[] = 'add_client';
        }

        // الفواتير
        if (\Illuminate\Support\Facades\DB::table('invoices')
            ->where('user_id', $user->id)->exists()) {
            $completed[] = 'create_invoice';
        }

        if ($user->transactions()->exists()) {
            $completed[] = 'add_transaction';
        }

        if ($user->budgets()->exists()) {
            $completed[] = 'set_budget';
        }

        // التقارير: تُعتبر مكتملة إذا أكمل خطوات الدخل والمشاريع
        if (in_array('add_transaction', $completed) && in_array('create_project', $completed)) {
            $completed[] = 'view_reports';
        }

        return $completed;
    }
}
