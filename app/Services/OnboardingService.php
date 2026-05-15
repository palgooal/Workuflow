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
            'key'         => 'add_transaction',
            'title'       => 'سجّل أول معاملة',
            'description' => 'أضف دخلاً أو مصروفاً لتبدأ بتتبع ماليتك',
            'icon'        => '💸',
            'url_name'    => 'transactions.create',
            'url_label'   => 'إضافة معاملة',
        ],
        [
            'key'         => 'set_budget',
            'title'       => 'ضع ميزانيتك الشهرية',
            'description' => 'حدد سقف المصروفات لكل فئة وتحكم في إنفاقك',
            'icon'        => '🎯',
            'url_name'    => 'budget.index',
            'url_label'   => 'ضبط الميزانية',
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

        if ($user->transactions()->exists()) {
            $completed[] = 'add_transaction';
        }

        if ($user->budgets()->exists()) {
            $completed[] = 'set_budget';
        }

        // التقارير: تُعتبر مكتملة إذا أكمل خطوات الدخل والمصروفات
        if (in_array('add_transaction', $completed) && in_array('create_project', $completed)) {
            $completed[] = 'view_reports';
        }

        return $completed;
    }
}
