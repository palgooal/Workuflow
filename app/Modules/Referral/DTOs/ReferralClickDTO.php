<?php

namespace App\Modules\Referral\DTOs;

/**
 * ReferralClickDTO — بيانات تسجيل نقرة على رابط إحالة
 *
 * DTO = Data Transfer Object: نقل بيانات فقط، لا سلوك.
 * مصدره: TrackReferralCode Middleware (المرحلة 6)
 *
 * visitor_token: ULID ثابت من Cookie مؤمَّنة (secure, httpOnly, sameSite=lax)
 * — مستقل عن IP/User-Agent/Date، لا تصادم على NAT
 */
final readonly class ReferralClickDTO
{
    public function __construct(
        public string  $affiliateId,     // CHAR(26) — affiliates.id
        public string  $visitorToken,    // ULID من Cookie 'referral_visitor_token'
        public ?string $ipAddress  = null,
        public ?string $userAgent  = null,
        public ?string $landingPage = null,
    ) {}
}
