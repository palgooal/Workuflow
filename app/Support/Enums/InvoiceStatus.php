<?php

namespace App\Support\Enums;

enum InvoiceStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Paid      = 'paid';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'مسودة',
            self::Sent      => 'مُرسَلة',
            self::Paid      => 'مدفوعة',
            self::Overdue   => 'متأخرة',
            self::Cancelled => 'ملغاة',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft     => 'bg-gray-100 text-gray-600',
            self::Sent      => 'bg-blue-100 text-blue-700',
            self::Paid      => 'bg-teal-100 text-teal-700',
            self::Overdue   => 'bg-red-100 text-red-700',
            self::Cancelled => 'bg-gray-100 text-gray-400',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Draft     => '📝',
            self::Sent      => '📤',
            self::Paid      => '✅',
            self::Overdue   => '⚠️',
            self::Cancelled => '❌',
        };
    }
}
