<?php

namespace App\Support\Enums;

enum QuoteStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Viewed    = 'viewed';      // العميل فتح الرابط
    case Accepted  = 'accepted';
    case Rejected  = 'rejected';
    case Expired   = 'expired';
    case Converted = 'converted';   // تحوّل إلى فاتورة

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'مسودة',
            self::Sent      => 'مُرسَل',
            self::Viewed    => 'تمت المشاهدة',
            self::Accepted  => 'مقبول',
            self::Rejected  => 'مرفوض',
            self::Expired   => 'منتهي الصلاحية',
            self::Converted => 'محوّل لفاتورة',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft     => 'bg-gray-100 text-gray-600',
            self::Sent      => 'bg-blue-100 text-blue-700',
            self::Viewed    => 'bg-indigo-100 text-indigo-700',
            self::Accepted  => 'bg-teal-100 text-teal-700',
            self::Rejected  => 'bg-red-100 text-red-700',
            self::Expired   => 'bg-orange-100 text-orange-700',
            self::Converted => 'bg-purple-100 text-purple-700',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Draft     => '📝',
            self::Sent      => '📤',
            self::Viewed    => '👁️',
            self::Accepted  => '✅',
            self::Rejected  => '❌',
            self::Expired   => '⏰',
            self::Converted => '🧾',
        };
    }

    /** هل يمكن تعديل العرض؟ */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    /** هل يمكن إرساله للعميل؟ */
    public function canBeSent(): bool
    {
        return in_array($this, [self::Draft, self::Sent, self::Viewed]);
    }

    /** هل يمكن تحويله لفاتورة؟ */
    public function canConvert(): bool
    {
        return $this === self::Accepted;
    }

    /** هل العرض في انتظار رد العميل؟ */
    public function isPending(): bool
    {
        return in_array($this, [self::Sent, self::Viewed]);
    }
}
