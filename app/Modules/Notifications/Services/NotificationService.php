<?php

namespace App\Modules\Notifications\Services;

use App\Models\Debt;
use App\Models\User;
use App\Notifications\DebtDueSoonNotification;
use App\Notifications\DebtOverdueNotification;
use App\Support\Enums\DebtStatus;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * فحص وتوليد الإشعارات التلقائية للديون (يُستدعى مرة في الجلسة)
     * يتجنب الإشعارات المكررة خلال 24 ساعة لنفس الدين
     */
    public function generateDebtAlerts(User $user): void
    {
        // الديون المستحقة خلال 7 أيام
        $dueSoon = Debt::where('user_id', $user->id)
            ->dueSoon(7)
            ->where('status', '!=', DebtStatus::Paid)
            ->get();

        foreach ($dueSoon as $debt) {
            $this->notifyOnce($user, $debt, 'debt_due_soon', new DebtDueSoonNotification($debt));
        }

        // الديون المتأخرة
        $overdue = Debt::where('user_id', $user->id)
            ->active()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->get();

        foreach ($overdue as $debt) {
            $this->notifyOnce($user, $debt, 'debt_overdue', new DebtOverdueNotification($debt));
        }
    }

    /**
     * إشعارات المستخدم غير المقروءة
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * جميع الإشعارات مع Pagination
     */
    public function getPaginated(User $user, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $user->notifications()->paginate($perPage);
    }

    /**
     * تحديد إشعار معين كمقروء
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->first()?->markAsRead();
    }

    /**
     * تحديد كل الإشعارات كمقروءة
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * حذف إشعار
     */
    public function delete(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->delete();
    }

    /**
     * حذف الإشعارات القديمة (أكثر من 30 يوماً)
     */
    public function deleteOld(User $user): int
    {
        return $user->notifications()
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    // ==================== Private ====================

    /**
     * ينشئ إشعاراً فقط إذا لم يُنشأ في آخر 24 ساعة لنفس الدين
     */
    private function notifyOnce(User $user, Debt $debt, string $type, object $notification): void
    {
        $alreadyNotified = $user->notifications()
            ->where('type', 'LIKE', "%{$type}%")
            ->where('created_at', '>=', now()->subHours(24))
            ->get()
            ->contains(function ($n) use ($debt) {
                $data = json_decode($n->data, true);
                return ($data['debt_id'] ?? null) === $debt->id;
            });

        if (!$alreadyNotified) {
            $user->notify($notification);
        }
    }
}
