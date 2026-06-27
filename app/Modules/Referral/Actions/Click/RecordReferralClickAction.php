<?php

namespace App\Modules\Referral\Actions\Click;

use App\Modules\Referral\DTOs\ReferralClickDTO;
use App\Modules\Referral\Models\ReferralClick;
use Illuminate\Support\Facades\Log;

/**
 * RecordReferralClickAction — تسجيل نقرة على رابط إحالة
 *
 * يُستدعى من: TrackReferralCode Middleware (المرحلة 6)
 *
 * قاعدة أساسية: لا يَرمي استثناء أبداً.
 * التتبع لا يجوز أن يكسر تجربة المستخدم أو يُوقف صفحة الـ Landing.
 * يُسجَّل Log::warning عند الفشل فقط.
 *
 * HasUlids في Model يولّد الـ id تلقائياً — لا حاجة لتعيينه يدوياً.
 */
class RecordReferralClickAction
{
    public function execute(ReferralClickDTO $dto): ?ReferralClick
    {
        try {
            return ReferralClick::create([
                'affiliate_id'  => $dto->affiliateId,
                'visitor_token' => $dto->visitorToken,
                'ip_address'    => $dto->ipAddress,
                'user_agent'    => $dto->userAgent,
                'landing_page'  => $dto->landingPage,
            ]);
        } catch (\Throwable $e) {
            Log::warning('RecordReferralClick failed — click not recorded', [
                'affiliate_id'  => $dto->affiliateId,
                'visitor_token' => $dto->visitorToken,
                'error'         => $e->getMessage(),
            ]);

            return null;
        }
    }
}
