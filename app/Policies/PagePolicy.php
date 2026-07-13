<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

/**
 * PagePolicy — طبقة تفويض صريحة لإدارة الصفحات.
 *
 * ملاحظة: لوحة /admin بأكملها محمية أصلاً بـ User::canAccessPanel()
 * (super_admin + حساب فعّال فقط)، لذا هذه السياسة طبقة توثيق/دفاع إضافي
 * صريحة، وتضيف القاعدة الوحيدة غير المضمونة أصلاً على مستوى اللوحة:
 * منع الحذف النهائي لصفحة قانونية سبق نشرها.
 */
class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->hasRole('super_admin');
    }

    /** حذف (Soft Delete) — ممنوع لصفحة قانونية سبق نشرها؛ استخدم الأرشفة بدلاً من ذلك */
    public function delete(User $user, Page $page): bool
    {
        return $user->hasRole('super_admin') && $page->canBeForceDeleted();
    }

    public function restore(User $user, Page $page): bool
    {
        return $user->hasRole('super_admin');
    }

    /** حذف نهائي — ممنوع دائماً لصفحة قانونية سبق نشرها */
    public function forceDelete(User $user, Page $page): bool
    {
        return $user->hasRole('super_admin') && $page->canBeForceDeleted();
    }
}
