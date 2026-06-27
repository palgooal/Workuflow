<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * ReferralRedirectController — توجيه المستخدم بعد تسجيل نقرة الإحالة
 *
 * يُستدعى فقط بعد نجاح TrackReferralCode Middleware
 * (المسوّق موجود + نشط + ليس spam + النقرة سُجِّلت)
 *
 * التوجيه:
 *  - إذا ?plan=pro|business  → register مع plan_intent (ملء الحقل المخفي تلقائياً)
 *  - الافتراضي              → register بدون plan_intent
 */
class ReferralRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $plan = $request->query('plan');

        if (in_array($plan, ['pro', 'business'], true)) {
            return redirect()->route('register', ['plan_intent' => $plan]);
        }

        return redirect()->route('register');
    }
}
