<?php

namespace App\Support\Enums;

/**
 * BackupTrigger — مصدر إنشاء النسخة الاحتياطية: يدوي (زر "إنشاء نسخة يدوية"
 * في Filament) أو مجدول (ScheduledBackupRunner عبر Laravel Scheduler).
 *
 * ⚠️ عمداً string enum وليس boolean — راجع طلب "المرحلة الخامسة" الذي ينص
 * صراحة على عدم استخدام Boolean لهذا العمود، حتى يمكن إضافة مصادر أخرى
 * مستقبلاً (مثلاً: API) دون Migration جديدة لتغيير نوع العمود.
 */
enum BackupTrigger: string
{
    case Manual = 'manual';
    case Scheduled = 'scheduled';

    public function label(): string
    {
        return match ($this) {
            self::Manual    => 'يدوي',
            self::Scheduled => 'مجدول',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Manual    => 'gray',
            self::Scheduled => 'info',
        };
    }
}
