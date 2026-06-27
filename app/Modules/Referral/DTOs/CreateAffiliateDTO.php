<?php

namespace App\Modules\Referral\DTOs;

/**
 * CreateAffiliateDTO — بيانات إنشاء حساب مسوّق جديد
 *
 * DTO = Data Transfer Object: نقل بيانات فقط، لا سلوك.
 * مصدره: AffiliateController::store() (المرحلة 8)
 */
final readonly class CreateAffiliateDTO
{
    public function __construct(
        public string  $name,
        public string  $email,
        public ?int    $userId       = null,    // bigint — users.id (اختياري: مسوّق قد يكون خارجياً)
        public ?string $whatsapp     = null,
        public ?string $displayCode  = null,    // مثل AHMED2026 — فريد، اختياري
        public ?string $notes        = null,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id'      => $this->userId,
            'name'         => $this->name,
            'email'        => $this->email,
            'whatsapp'     => $this->whatsapp,
            'display_code' => $this->displayCode,
            'notes'        => $this->notes,
            // القيم الافتراضية: status=pending, tier=standard, commission_rate=30.00
        ];
    }
}
