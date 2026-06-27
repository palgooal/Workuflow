<?php

namespace App\Http\Middleware;

use App\Modules\Referral\Actions\Click\RecordReferralClickAction;
use App\Modules\Referral\DTOs\ReferralClickDTO;
use App\Modules\Referral\Services\FraudDetectionService;
use App\Modules\Referral\Services\ReferralService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * TrackReferralCode — Middleware لتتبع نقرات الإحالة وربطها بالجلسة
 *
 * يُطبَّق على: Route::get('/ref/{identifier}')
 *
 * التدفق:
 *  1. حلّ هوية المسوّق (ULID أو display_code) — إذا لم يُوجد → redirect('/')
 *  2. التحقق من spam (≥20 نقرة من نفس IP يومياً) — إذا spam → redirect('/') بدون تسجيل
 *  3. جلب أو توليد visitor_token من Cookie
 *  4. تسجيل النقرة عبر RecordReferralClickAction
 *  5. تخزين affiliate_id + click_id في Session (للجلسة الحالية)
 *  6. وضع Cookies (60 يوم) للاستخدام عند التسجيل المؤجَّل
 *
 * Cookie Strategy:
 *  - ref_aff     : ULID المسوّق (60 يوم) — يُقرأ عند التسجيل
 *  - ref_clk     : ULID سجل النقرة (60 يوم) — يُقرأ عند التسجيل
 *  - ref_visitor : visitor_token (60 يوم) — لكشف الحسابات المزدوجة
 *
 * عند التسجيل: RegisterUserAction يقرأ من Session أولاً، ثم من Cookies كـ fallback
 */
class TrackReferralCode
{
    /** مدة الكوكي بالدقائق (60 يوم) */
    private const COOKIE_MINUTES = 60 * 24 * 60;

    public function __construct(
        private readonly ReferralService       $referralService,
        private readonly FraudDetectionService $fraudService,
        private readonly RecordReferralClickAction $recordClick,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $identifier = (string) $request->route('identifier');

        // ── [1] حلّ هوية المسوّق ─────────────────────────────────────────
        $affiliate = $this->referralService->resolveAffiliate($identifier);

        if (! $affiliate || ! $affiliate->isActive()) {
            Log::info('Referral: unknown or inactive identifier', [
                'identifier' => $identifier,
                'ip'         => $request->ip(),
            ]);
            return redirect('/');
        }

        // ── [2] كشف Click Spam ───────────────────────────────────────────
        if ($this->fraudService->detectClickSpam($request->ip())) {
            Log::warning('Referral: click spam detected — not recording', [
                'affiliate_id' => $affiliate->id,
                'ip'           => $request->ip(),
            ]);
            // لا نكشف للمستخدم أن النقرة رُفضت — redirect طبيعي
            return redirect('/');
        }

        // ── [3] visitor_token ────────────────────────────────────────────
        // يُعرَّف البراوزر بنفس التوكن عبر جميع الزيارات (60 يوم)
        $visitorToken = $request->cookie('ref_visitor') ?: Str::ulid()->toString();

        // ── [4] تسجيل النقرة ─────────────────────────────────────────────
        $click = $this->recordClick->execute(new ReferralClickDTO(
            affiliateId:  $affiliate->id,
            visitorToken: $visitorToken,
            ipAddress:    $request->ip(),
            userAgent:    $request->userAgent() ?? '',
            landingPage:  $request->fullUrl(),
        ));

        // ── [5] Session (للجلسة الحالية — التسجيل الفوري) ────────────────
        $request->session()->put('referral_affiliate_id', $affiliate->id);

        if ($click) {
            $request->session()->put('referral_click_id', $click->id);
        }

        // ── [6] Queue Cookies (60 يوم — للتسجيل المؤجَّل) ────────────────
        $domain  = config('session.domain');
        $secure  = true;
        $httpOnly = true;
        $sameSite = 'lax';

        // visitor_token — دائماً (لكشف الحسابات المزدوجة مستقبلاً)
        Cookie::queue(cookie(
            name:     'ref_visitor',
            value:    $visitorToken,
            minutes:  self::COOKIE_MINUTES,
            path:     '/',
            domain:   $domain,
            secure:   $secure,
            httpOnly: $httpOnly,
            raw:      false,
            sameSite: $sameSite,
        ));

        // affiliate_id — دائماً (يُقرأ عند التسجيل)
        Cookie::queue(cookie(
            name:     'ref_aff',
            value:    $affiliate->id,
            minutes:  self::COOKIE_MINUTES,
            path:     '/',
            domain:   $domain,
            secure:   $secure,
            httpOnly: $httpOnly,
            raw:      false,
            sameSite: $sameSite,
        ));

        // click_id — فقط إذا سُجِّلت النقرة بنجاح
        if ($click) {
            Cookie::queue(cookie(
                name:     'ref_clk',
                value:    $click->id,
                minutes:  self::COOKIE_MINUTES,
                path:     '/',
                domain:   $domain,
                secure:   $secure,
                httpOnly: $httpOnly,
                raw:      false,
                sameSite: $sameSite,
            ));
        }

        return $next($request);
    }
}
