<?php

namespace App\Support\Enums;

enum RecurringFrequency: string
{
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    public function label(): string
    {
        return match($this) {
            self::Daily   => 'يومي',
            self::Weekly  => 'أسبوعي',
            self::Monthly => 'شهري',
            self::Yearly  => 'سنوي',
        };
    }

    /**
     * حساب تاريخ الاستحقاق القادم بناءً على التكرار
     */
    public function nextDate(\Carbon\Carbon $from): \Carbon\Carbon
    {
        return match($this) {
            self::Daily   => $from->addDay(),
            self::Weekly  => $from->addWeek(),
            self::Monthly => $from->addMonth(),
            self::Yearly  => $from->addYear(),
        };
    }
}
