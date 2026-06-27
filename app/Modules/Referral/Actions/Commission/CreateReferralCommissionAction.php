<?php

namespace App\Modules\Referral\Actions\Commission;

use App\Modules\Referral\DTOs\CreateCommissionDTO;
use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralCommission;
use App\Modules\Referral\Notifications\NewCommissionEarnedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CreateReferralCommissionAction — إنشاء سجل عمولة واحد لاشتراك واحد
 *
 * يُستدعى حصراً من: CreateReferralCommission Listener (المرحلة 5)
 *
 * الضمانات:
 *  - UNIQUE INDEX على subscription_id في DB يمنع التكرار على مستوى قاعدة البيانات
 *  - total_converted يُزاد فورياً للترقية الفورية للـ Tier
 *  - total_earned لا يُزاد هنا — فقط عند approve (حالة pending لا تُحتسب مالياً)
 *  - UpgradeAffiliateTierAction يُستدعى بعد كل عمولة
 *
 * دورة الحياة: pending → (7 أيام) → approved → paid
 */
class CreateReferralCommissionAction
{
    public function __construct(
        private readonly UpgradeAffiliateTierAction $upgradeTier,
    ) {}

    public function execute(CreateCommissionDTO $dto): ReferralCommission
    {
        $commission = DB::transaction(function () use ($dto): ReferralCommission {

            // 1. إنشاء سجل العمولة
            $commission = ReferralCommission::create($dto->toArray());

            // 2. تحديث total_converted فوراً (pending يُحتسب للـ Tier — راجع §15)
            //    يُصحَّح يومياً بـ php artisan referral:reconcile
            Affiliate::where('id', $dto->affiliateId)
                ->increment('total_converted');

            // 3. ترقية الـ Tier إن استحق
            $affiliate = Affiliate::find($dto->affiliateId);

            if ($affiliate) {
                $this->upgradeTier->execute($affiliate->fresh());
            }

            Log::info('ReferralCommission created', [
                'commission_id'   => $commission->id,
                'affiliate_id'    => $dto->affiliateId,
                'subscription_id' => $dto->subscriptionId,
                'amount'          => $dto->amount,
                'fraud_flagged'   => $dto->fraudFlagged,
            ]);

            return $commission;
        });

        // 4. إشعار المسوّق خارج الـ Transaction (لا يؤثر على صحة البيانات عند فشله)
        $affiliate = Affiliate::find($dto->affiliateId);
        $affiliate?->user?->notify(new NewCommissionEarnedNotification($commission));

        return $commission;
    }
}
