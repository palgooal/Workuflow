<?php

namespace App\Modules\Referral\Actions\Payout;

use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralPayout;
use App\Modules\Referral\Services\ReferralService;
use Illuminate\Support\Facades\Log;

/**
 * CreatePayoutRequestAction — إنشاء طلب صرف رصيد للمسوّق
 *
 * يُستدعى من: AffiliateController::requestPayout()
 *
 * الشروط (تُفحَص في ReferralService::canRequestPayout):
 *  - الرصيد >= 20 USD
 *  - لا يوجد طلب معلّق (requested/processing)
 *
 * المبلغ = balance (total_earned - total_paid) — الرصيد المتاح كاملاً
 * ℹ️  total_paid لا يُزاد هنا — يُزاد بواسطة الأدمن عند الإنجاز (المرحلة 9)
 */
class CreatePayoutRequestAction
{
    public function __construct(
        private readonly ReferralService $referralService,
    ) {}

    public function execute(Affiliate $affiliate, string $method, ?string $notes = null): ReferralPayout
    {
        if (! $this->referralService->canRequestPayout($affiliate)) {
            throw new \RuntimeException(
                'لا يمكن إنشاء طلب صرف: الرصيد أقل من 20 USD أو يوجد طلب معلّق.'
            );
        }

        $payout = ReferralPayout::create([
            'affiliate_id' => $affiliate->id,
            'amount'       => $affiliate->balance,
            'currency'     => 'USD',
            'method'       => $method,
            'status'       => 'requested',
            'notes'        => $notes,
        ]);

        Log::info('Referral: payout requested', [
            'payout_id'    => $payout->id,
            'affiliate_id' => $affiliate->id,
            'amount'       => $payout->amount,
            'method'       => $method,
        ]);

        return $payout;
    }
}
