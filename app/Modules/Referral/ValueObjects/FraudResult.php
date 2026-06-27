<?php

namespace App\Modules\Referral\ValueObjects;

/**
 * FraudResult — نتيجة فحص الاحتيال
 *
 * Value Object وفق DDD: immutable + سلوك مدمج (clean/flagged).
 * يختلف عن DTO الذي هو carrier للبيانات فقط بلا سلوك.
 *
 * | المعيار     | DTO              | Value Object (FraudResult)       |
 * |------------|------------------|----------------------------------|
 * | الغرض      | نقل بيانات       | تمثيل مفهوم نطاقي (domain)      |
 * | السلوك     | لا سلوك          | clean() / flagged()              |
 * | Immutability | اختياري        | إلزامي (final + readonly)        |
 * | الموقع     | DTOs/            | ValueObjects/                    |
 *
 * الاستخدام:
 *   $result = $fraudService->detectSuspiciousConversions($affiliate, $user);
 *   if ($result->isFlagged) { ... }
 */
final class FraudResult
{
    private function __construct(
        public readonly bool  $isFlagged,
        public readonly array $reasons = [],
    ) {}

    /** نتيجة نظيفة — لا احتيال مكتشف */
    public static function clean(): self
    {
        return new self(isFlagged: false);
    }

    /** نتيجة مشبوهة — مع قائمة الأسباب */
    public static function flagged(array $reasons): self
    {
        return new self(isFlagged: true, reasons: $reasons);
    }

    /** هل النتيجة نظيفة؟ */
    public function isClean(): bool
    {
        return ! $this->isFlagged;
    }

    /** أول سبب للاشتباه (أو null إن كانت نظيفة) */
    public function primaryReason(): ?string
    {
        return $this->reasons[0] ?? null;
    }
}
