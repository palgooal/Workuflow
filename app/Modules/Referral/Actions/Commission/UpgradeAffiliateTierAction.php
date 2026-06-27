<?php

namespace App\Modules\Referral\Actions\Commission;

use App\Modules\Referral\Enums\AffiliateTier;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Notifications\TierUpgradedNotification;
use Illuminate\Support\Facades\Log;

/**
 * UpgradeAffiliateTierAction — ترقية مستوى المسوّق بعد كل اشتراك جديد
 *
 * يُستدعى من: CreateReferralCommissionAction بعد تسجيل كل عمولة
 * أيضاً من: ReconcileReferralAggregatesCommand (المرحلة 7) كتصحيح يومي
 *
 * منطق الترقية (راجع §7):
 *  - total_converted >= 100 → Platinum (45%)
 *  - total_converted >= 30  → Gold (40%)
 *  - total_converted >= 10  → Silver (35%)
 *  - الباقي               → Standard (30%)
 *
 * قاعدة: idempotent — لا يُغيَّر شيء إذا كان المسوّق بالفعل في المستوى الصحيح.
 */
class UpgradeAffiliateTierAction
{
    public function execute(Affiliate $affiliate): void
    {
        $newTier = AffiliateTier::fromConversions($affiliate->total_converted);
        $newRate = $newTier->defaultRate();

        // لا تغيير — المسوّق بالفعل في المستوى الصحيح
        if ($affiliate->tier === $newTier) {
            return;
        }

        $oldTier = $affiliate->tier;

        $affiliate->update([
            'tier'            => $newTier->value,
            'commission_rate' => $newRate,
        ]);

        Log::info('Referral: affiliate tier upgraded', [
            'affiliate_id'    => $affiliate->id,
            'from_tier'       => $oldTier->value,
            'to_tier'         => $newTier->value,
            'new_rate'        => $newRate,
            'total_converted' => $affiliate->total_converted,
        ]);

        // إشعار المسوّق بترقية مستواه
        $affiliate->user?->notify(
            new TierUpgradedNotification(
                newTier: $newTier,
                oldTier: $oldTier,
                newRate: $newRate,
            )
        );
    }
}
