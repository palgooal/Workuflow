<?php

namespace App\Support\Enums;

enum DataExportStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
    case Expired    = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'قيد الانتظار',
            self::Processing => 'جارٍ التجهيز',
            self::Completed  => 'جاهز للتنزيل',
            self::Failed     => 'فشل',
            self::Expired    => 'منتهي الصلاحية',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'gray',
            self::Processing => 'info',
            self::Completed  => 'success',
            self::Failed     => 'danger',
            self::Expired    => 'gray',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Pending || $this === self::Processing;
    }

    public function isFinished(): bool
    {
        return ! $this->isActive();
    }
}
