<?php

namespace App\Modules\CRM\Enums;

/**
 * ImportStatus — حالة عملية الاستيراد
 */
enum ImportStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
    case Partial    = 'partial';   // اكتمل مع أخطاء في بعض الصفوف

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'في الانتظار',
            self::Processing => 'جارٍ المعالجة',
            self::Completed  => 'مكتمل',
            self::Failed     => 'فشل',
            self::Partial    => 'مكتمل جزئياً',
        };
    }

    public function progressColor(): string
    {
        return match($this) {
            self::Pending    => '#6B7280',   // gray
            self::Processing => '#3B82F6',   // blue
            self::Completed  => '#10B981',   // green
            self::Failed     => '#EF4444',   // red
            self::Partial    => '#F59E0B',   // amber
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending    => 'bg-gray-100 text-gray-600',
            self::Processing => 'bg-blue-100 text-blue-700',
            self::Completed  => 'bg-green-100 text-green-800',
            self::Failed     => 'bg-red-100 text-red-800',
            self::Partial    => 'bg-amber-100 text-amber-800',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Pending    => '⏳',
            self::Processing => '⚙️',
            self::Completed  => '✅',
            self::Failed     => '❌',
            self::Partial    => '⚠️',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Partial]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
