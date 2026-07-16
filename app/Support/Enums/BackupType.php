<?php

namespace App\Support\Enums;

enum BackupType: string
{
    case Database = 'database';
    case Full     = 'full';

    public function label(): string
    {
        return match ($this) {
            self::Database => 'قاعدة بيانات فقط',
            self::Full     => 'نسخة كاملة (قاعدة بيانات + ملفات)',
        };
    }
}
