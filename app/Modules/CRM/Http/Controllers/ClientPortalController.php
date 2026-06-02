<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Models\ClientPortalToken;
use App\Modules\CRM\Enums\PortalPermission;
use App\Modules\CRM\Services\ClientPortalTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * ClientPortalController — بوابة العميل
 *
 * ⚠️ أمان حرج (C-04 Fix من CLIENTS-CRM-SPEC-V2.md):
 * - Token مخزن كـ hash('sha256', $plaintext) في DB
 * - Rate limiting: 5 محاولات/ساعة بـ IP
 * - Artificial delay عند الفشل لمنع timing attacks
 * - الجلسة تحتفظ فقط بـ ID (لا بالرمز)
 */
class ClientPortalController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 3600;    // ساعة

    // ==================== المصادقة ====================

    public function showAuthForm(Request $request): View
    {
        return view('portal.auth', [
            'prefillToken' => $request->query('token'),
        ]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $ip      = $request->ip();
        $limiterKey = "portal:auth:{$ip}";

        // فحص Rate Limit
        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return back()->withErrors([
                'token' => "تجاوزت عدد المحاولات المسموح بها. حاول مجدداً بعد " . ceil($seconds / 60) . " دقيقة.",
            ]);
        }

        $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $plaintext = $request->input('token');
        $hash      = hash('sha256', $plaintext);

        $token = ClientPortalToken::where('token', $hash)
            ->where('expires_at', '>', now())
            ->with('client.user')
            ->first();

        if (!$token) {
            // تأخير اصطناعي لمنع timing attacks
            usleep(random_int(80_000, 200_000));
            RateLimiter::hit($limiterKey, self::DECAY_SECONDS);

            return back()->withErrors([
                'token' => 'الرمز غير صحيح أو منتهي الصلاحية.',
            ])->withInput();
        }

        // نجحت المصادقة — إعادة تعيين Rate Limiter
        RateLimiter::clear($limiterKey);

        // تسجيل الدخول: حفظ ID الرمز فقط في الجلسة
        session()->regenerate();
        session(['client_portal_token' => $token->id]);

        // تحديث بيانات الاستخدام
        $token->update([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
        ]);

        return redirect()->route('portal.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget('client_portal_token');
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('portal.auth')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }

    public function showAccessForm(): View
    {
        return view('portal.access');
    }

    // ==================== لوحة العميل (محمي بـ portal.auth) ====================

    public function dashboard(Request $request): View
    {
        /** @var ClientPortalToken $portalToken */
        $portalToken = $request->attributes->get('portal_token');
        $client      = $request->attributes->get('portal_client');

        // بيانات الملخص من أعمدة المجمّعات في clients
        $summary = [
            'total_revenue'   => (float) $client->total_revenue,
            'total_paid'      => (float) $client->total_paid,
            'outstanding'     => (float) $client->total_revenue - (float) $client->total_paid,
            'invoice_count'   => (int) $client->invoice_count,
            'last_payment_at' => $client->last_payment_at,
        ];

        // المشاريع المرتبطة بالعميل
        $projects = $client->projects()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'created_at', 'color']);

        return view('portal.dashboard', compact('client', 'portalToken', 'summary', 'projects'));
    }

    public function invoices(Request $request): View
    {
        $portalToken = $request->attributes->get('portal_token');
        $client      = $request->attributes->get('portal_client');

        // التحقق من صلاحية عرض الفواتير
        if (!$portalToken->hasPermission(PortalPermission::ViewInvoices)) {
            return view('portal.error', [
                'message' => 'ليس لديك صلاحية لعرض الفواتير.',
                'client'  => $client,
            ]);
        }

        // المعاملات المرتبطة بمشاريع هذا العميل
        $transactions = \App\Models\Transaction::whereHas('project', fn ($q) =>
            $q->where('client_id', $client->id)
        )
        ->with('project:id,name')
        ->orderByDesc('transaction_date')
        ->get();

        $canDownload = $portalToken->hasPermission(PortalPermission::DownloadInvoices);

        return view('portal.invoices.index', compact('client', 'portalToken', 'transactions', 'canDownload'));
    }

    public function invoiceShow(Request $request, int $id): View
    {
        $portalToken = $request->attributes->get('portal_token');
        $client      = $request->attributes->get('portal_client');

        if (!$portalToken->hasPermission(PortalPermission::ViewInvoices)) {
            return view('portal.error', [
                'message' => 'ليس لديك صلاحية لعرض هذه الفاتورة.',
                'client'  => $client,
            ]);
        }

        $transaction = \App\Models\Transaction::whereHas('project', fn ($q) =>
            $q->where('client_id', $client->id)
        )
        ->with('project:id,name')
        ->findOrFail($id);

        $canDownload = $portalToken->hasPermission(PortalPermission::DownloadInvoices);

        return view('portal.invoices.show', compact('client', 'portalToken', 'transaction', 'canDownload'));
    }

    public function profile(Request $request): View
    {
        $portalToken = $request->attributes->get('portal_token');
        $client      = $request->attributes->get('portal_client');

        return view('portal.profile', compact('client', 'portalToken'));
    }
}
