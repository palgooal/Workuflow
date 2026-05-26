<?php

namespace App\Http\Middleware;

use App\Modules\CRM\Models\ClientPortalToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsurePortalAuthenticated
 *
 * يتحقق من أن زائر بوابة العميل لديه جلسة صالحة.
 * المرجع: docs/CLIENTS-CRM-SPEC-V2.md — C-04 Fix
 *
 * الأمان:
 * - الرمز مخزن كـ hash(sha256) في قاعدة البيانات
 * - الجلسة تحتفظ فقط بـ ID الرمز (لا بالرمز نفسه)
 * - التحقق من انتهاء الصلاحية في كل طلب
 */
class EnsurePortalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $tokenId = session('client_portal_token');

        if (!$tokenId) {
            return redirect()->route('portal.auth')
                ->with('error', 'يجب تسجيل الدخول للوصول إلى البوابة.');
        }

        $token = ClientPortalToken::find($tokenId);

        if (!$token || $token->expires_at->isPast()) {
            session()->forget('client_portal_token');

            return redirect()->route('portal.auth')
                ->with('error', 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مجدداً.');
        }

        // تحديث آخر استخدام (كل 10 دقائق لتجنب writes متكررة)
        if (!$token->last_used_at || $token->last_used_at->diffInMinutes(now()) >= 10) {
            $token->update(['last_used_at' => now()]);
        }

        // مشاركة بيانات العميل مع الـ Views
        $request->attributes->set('portal_token', $token);
        $request->attributes->set('portal_client', $token->client);

        view()->share('portalToken', $token);
        view()->share('portalClient', $token->client);

        return $next($request);
    }
}
