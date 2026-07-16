<?php

namespace App\Support\Enums;

enum BackupStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'قيد الانتظار',
            self::Running   => 'جارٍ التنفيذ',
            self::Completed => 'مكتملة',
            self::Failed    => 'فشلت',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'gray',
            self::Running   => 'info',
            self::Completed => 'success',
            self::Failed    => 'danger',
        };
    }
}
