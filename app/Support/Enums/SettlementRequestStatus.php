<?php

namespace App\Support\Enums;

/**
 * حالة طلب التسوية (SettlementRequest) — الذي يُنشئه المشترك ليطلب من دراهم
 * تحويل صافي التحصيلات الجاهزة (settlement_net_amount) إليه.
 *
 * pending  → طلب جديد، بانتظار مراجعة الأدمن
 * approved → الأدمن وافق على الطلب، بانتظار التحويل الفعلي (خارج النظام)
 * rejected → الأدمن رفض الطلب (admin_notes يوضّح السبب)
 * paid     → تم التحويل فعلياً + PaymentCollection المرتبطة أصبحت settled
 */
enum SettlementRequestStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid     = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'قيد المراجعة',
            self::Approved => 'مُعتمَد',
            self::Rejected => 'مرفوض',
            self::Paid     => 'مدفوع',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending  => 'bg-amber-100 text-amber-700',
            self::Approved => 'bg-blue-100 text-blue-700',
            self::Rejected => 'bg-red-100 text-red-700',
            self::Paid     => 'bg-emerald-100 text-emerald-700',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending  => '⏳',
            self::Approved => '👍',
            self::Rejected => '❌',
            self::Paid     => '✅',
        };
    }
}
