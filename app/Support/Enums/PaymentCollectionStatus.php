<?php

namespace App\Support\Enums;

/**
 * حالة عملية التحصيل عبر بوابة الدفع نيابة عن المشترك.
 *
 * pending   → طلب دفع أُنشئ ولم يُحصَّل بعد (المستخدم على صفحة الدفع أو البوابة)
 * collected → تم تحصيل المبلغ من العميل بنجاح (لدى دراهم، بانتظار التسوية)
 * settled   → تمت تسوية المبلغ يدوياً مع المشترك (تحويل فعلي)
 * failed    → فشلت عملية الدفع أو أُلغيت
 * refunded  → تم استرجاع المبلغ للعميل
 */
enum PaymentCollectionStatus: string
{
    case Pending   = 'pending';
    case Collected = 'collected';
    case Settled   = 'settled';
    case Failed    = 'failed';
    case Refunded  = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'قيد الانتظار',
            self::Collected => 'تم التحصيل',
            self::Settled   => 'تمت التسوية',
            self::Failed    => 'فشل',
            self::Refunded  => 'مُسترجَع',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending   => 'bg-amber-100 text-amber-700',
            self::Collected => 'bg-teal-100 text-teal-700',
            self::Settled   => 'bg-emerald-100 text-emerald-700',
            self::Failed    => 'bg-red-100 text-red-700',
            self::Refunded  => 'bg-slate-100 text-slate-500',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending   => '⏳',
            self::Collected => '💰',
            self::Settled   => '✅',
            self::Failed    => '❌',
            self::Refunded  => '↩️',
        };
    }
}
