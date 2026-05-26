<?php

namespace App\Modules\CRM\Policies;

use App\Models\Client;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ClientPolicy — صلاحيات العملاء مع دعم حدود الخطط
 *
 * مرجع: docs/CLIENTS-CRM-SPEC-V2.md — Sprint 1, S1.5
 * - owner check: $client->user_id === $user->id
 * - plan gating: config('crm.limits')[$plan->value]
 */
class ClientPolicy
{
    use HandlesAuthorization;

    // ==================== Helpers ====================

    /**
     * إرجاع إعدادات الخطة الحالية للمستخدم من config/crm.php
     */
    private function planLimits(User $user): array
    {
        $planKey = $user->currentPlan()->value; // 'free' | 'pro' | 'business'

        return config("crm.limits.{$planKey}", config('crm.limits.free'));
    }

    /**
     * هل الخطة Pro أو أعلى؟
     */
    private function isProOrHigher(User $user): bool
    {
        return in_array(
            $user->currentPlan(),
            [SubscriptionPlan::Pro, SubscriptionPlan::Business],
            strict: true
        );
    }

    /**
     * هل الخطة Business؟
     */
    private function isBusiness(User $user): bool
    {
        return $user->currentPlan() === SubscriptionPlan::Business;
    }

    // ==================== Gates ====================

    /**
     * عرض قائمة العملاء — أي مستخدم مسجّل دخوله
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * عرض عميل — المالك فقط
     */
    public function view(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * إنشاء عميل — مع فحص حد الخطة
     * Free: max 10 | Pro: max 500 | Business: غير محدود
     */
    public function create(User $user): bool
    {
        $limits = $this->planLimits($user);
        $max    = $limits['max_clients'] ?? 10;

        // -1 = غير محدود
        if ($max === -1) {
            return true;
        }

        $current = Client::where('user_id', $user->id)
                         ->where('is_archived', false)
                         ->count();

        return $current < $max;
    }

    /**
     * تعديل عميل — المالك فقط
     */
    public function update(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * حذف ناعم — المالك فقط
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * استعادة عميل محذوف — المالك فقط
     */
    public function restore(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * حذف دائم — المالك فقط
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * أرشفة / إلغاء أرشفة — المالك فقط
     */
    public function archive(User $user, Client $client): bool
    {
        return $user->id === $client->user_id;
    }

    /**
     * إدارة بوابة العميل — Business فقط
     */
    public function managePortal(User $user): bool
    {
        $limits = $this->planLimits($user);

        return (bool) ($limits['can_portal'] ?? false);
    }

    /**
     * استيراد العملاء — Pro+
     */
    public function importClients(User $user): bool
    {
        $limits = $this->planLimits($user);

        return (bool) ($limits['can_import'] ?? false);
    }

    /**
     * تصدير العملاء — Pro+
     */
    public function exportClients(User $user): bool
    {
        $limits = $this->planLimits($user);

        return (bool) ($limits['can_export'] ?? false);
    }

    /**
     * إدارة الحقول المخصصة — Pro+
     * (max_custom_fields > 0)
     */
    public function manageCustomFields(User $user): bool
    {
        $limits = $this->planLimits($user);
        $max    = $limits['max_custom_fields'] ?? 0;

        return $max === -1 || $max > 0;
    }

    /**
     * عرض التحليلات (Health Score + Segments) — Pro+
     */
    public function viewAnalytics(User $user): bool
    {
        $limits = $this->planLimits($user);

        return (bool) ($limits['can_health_score'] ?? false)
            || (bool) ($limits['can_segments']    ?? false);
    }
}
