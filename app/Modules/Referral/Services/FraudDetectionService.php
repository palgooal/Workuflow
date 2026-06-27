<?php

namespace App\Modules\Referral\Services;

use App\Models\User;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralClick;
use App\Modules\Referral\Models\ReferralCommission;
use App\Modules\Referral\ValueObjects\FraudResult;
use Illuminate\Support\Facades\Log;

/**
 * FraudDetectionService — كشف أنماط الاحتيال في برنامج الإحالات
 *
 * مبدأ التصميم: لا يُوقف أي تدفق — يُعيد نتيجة فقط.
 * القرار النهائي (رفض/قبول/تعليق) يعود للـ Action أو الأدمن.
 *
 * قواعد الفحص (راجع §17):
 *  [1] Click Spam:          >20 click من نفس IP في اليوم
 *  [2] Duplicate Accounts:  نفس visitor_token → تسجيلات متعددة
 *  [3] Self-referral:       المسوّق يسجّل من رابطه
 *  [4] High conversion rate: 5+ conversions اليوم → تنبيه الأدمن
 *  [5] TOR/VPN:             Phase 2 (يتطلب خدمة خارجية)
 */
class FraudDetectionService
{
    /**
     * [3] كشف Self-referral: المسوّق يسجّل من رابطه
     *
     * يُسجَّل في Log دائماً حتى لو لم يُطبَّق.
     * يُرجع true إذا انطبق الشرط.
     */
    public function detectSelfReferral(Affiliate $affiliate, User $user): bool
    {
        $isSelf = $affiliate->user_id !== null
            && $affiliate->user_id === $user->id;

        if ($isSelf) {
            Log::warning('Referral Fraud [self-referral]: affiliate tried to refer themselves', [
                'affiliate_id' => $affiliate->id,
                'user_id'      => $user->id,
            ]);
        }

        return $isSelf;
    }

    /**
     * [1] كشف Click Spam: أكثر من 20 click من نفس IP في يوم واحد
     *
     * لا يُوقف الطلب — يُرجع true إذا تجاوز الحد.
     * TrackReferralCode Middleware يستخدم هذا لتسجيل الـ click أو تجاهله.
     */
    public function detectClickSpam(string $ipAddress): bool
    {
        if (empty($ipAddress)) {
            return false;
        }

        $count = ReferralClick::where('ip_address', $ipAddress)
            ->whereDate('created_at', today())
            ->count();

        $isSpam = $count >= 20;

        if ($isSpam) {
            Log::warning('Referral Fraud [click-spam]: IP exceeded 20 clicks today', [
                'ip_address' => $ipAddress,
                'count'      => $count,
            ]);
        }

        return $isSpam;
    }

    /**
     * [2] كشف حسابات مكرّرة: نفس visitor_token → تسجيلات متعددة
     *
     * يُرجع true إذا سُجَّل أكثر من مستخدم واحد بنفس الـ visitor_token.
     */
    public function detectDuplicateAccounts(string $visitorToken): bool
    {
        $count = User::whereHas('referralClick', fn ($q) =>
            $q->where('visitor_token', $visitorToken)
        )->count();

        $isDuplicate = $count > 1;

        if ($isDuplicate) {
            Log::warning('Referral Fraud [duplicate-account]: visitor_token linked to multiple users', [
                'visitor_token' => $visitorToken,
                'count'         => $count,
            ]);
        }

        return $isDuplicate;
    }

    /**
     * [4] كشف أنماط مشبوهة في التحويل
     *
     * يُفحص:
     *  - ارتفاع غير طبيعي: 5+ conversions من نفس المسوّق اليوم
     *  - (Phase 2: تطابق payout_method بين المسوّق والمُحال)
     *
     * يُرجع FraudResult::flagged() أو FraudResult::clean()
     *
     * ⚠️  FraudFlaggedNotification ستُضاف في المرحلة 10.
     *     حالياً يُستخدم Log::warning كبديل مؤقت.
     */
    public function detectSuspiciousConversions(
        Affiliate $affiliate,
        User      $user,
    ): FraudResult {
        $reasons = [];

        // [4a] ارتفاع غير طبيعي في التحويلات
        $todayConversions = ReferralCommission::where('affiliate_id', $affiliate->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayConversions >= 5) {
            $reason    = "high_conversion_rate: {$todayConversions} conversions today";
            $reasons[] = $reason;

            // TODO Phase 10: Notification::route('mail', config('mail.admin_address'))
            //                ->notify(new FraudFlaggedNotification($affiliate, $reasons));
            Log::warning('Referral Fraud [high-conversion]: affiliate flagged for admin review', [
                'affiliate_id'      => $affiliate->id,
                'today_conversions' => $todayConversions,
                'reason'            => $reason,
            ]);
        }

        // [4b] Phase 2: تطابق payout_method — يُفعَّل لاحقاً
        // إذا كانت للمسوّق والمستخدم نفس بيانات الصرف يُضاف هنا.

        return empty($reasons)
            ? FraudResult::clean()
            : FraudResult::flagged($reasons);
    }
}
