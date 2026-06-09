<?php

namespace App\Http\Controllers;

use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $billing
    ) {}

    /**
     * صفحة الأسعار وإدارة الاشتراك الحالي
     */
    public function index(): View
    {
        $user         = auth()->user();
        $subscription = $this->billing->getCurrentSubscription($user);
        $planPrices   = $this->billing->getPlanPrices();
        $currentPlan  = $user->currentPlan();
        $providerReady = $this->billing->isPaymentProviderConfigured();

        return view('billing.index', compact(
            'user', 'subscription', 'planPrices', 'currentPlan', 'providerReady'
        ));
    }

    /**
     * Checkout — يحتاج مزود دفع مفعّل
     * TODO: عند إضافة المزود: inject PaymentProviderInterface واستدعِ createCheckoutUrl()
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'in:pro,business'],
        ]);

        if (! $this->billing->isPaymentProviderConfigured()) {
            return back()->with('info', 'بوابة الدفع غير مفعّلة بعد. تواصل مع الدعم لترقية خطتك.');
        }

        // TODO: استدعاء مزود الدفع هنا
        // $url = app(PaymentProviderInterface::class)->createCheckoutUrl(auth()->user(), $request->plan);
        // return redirect($url);

        return back()->with('info', 'سيتم ربط بوابة الدفع قريباً.');
    }

    /**
     * صفحة نجاح الدفع (يُستدعى بعد redirect من مزود الدفع)
     */
    public function success(Request $request): View
    {
        return view('billing.success', [
            'plan' => auth()->user()->currentPlan(),
        ]);
    }

    /**
     * بوابة إدارة الاشتراك
     * TODO: عند إضافة المزود: استدعِ createPortalUrl()
     */
    public function portal(): RedirectResponse
    {
        if (! $this->billing->isPaymentProviderConfigured()) {
            return back()->with('info', 'بوابة الدفع غير مفعّلة بعد.');
        }

        // TODO: redirect($provider->createPortalUrl(auth()->user()));
        return back()->with('info', 'سيتم ربط بوابة الدفع قريباً.');
    }

    /**
     * صفحة الترقية اليدوية — تواصل معنا على واتساب
     */
    public function upgrade(): View
    {
        $currentPlan   = auth()->user()->currentPlan();
        $ownerWhatsapp = config('billing.owner_whatsapp');
        $planPrices    = $this->billing->getPlanPrices();

        return view('billing.upgrade', compact('currentPlan', 'ownerWhatsapp', 'planPrices'));
    }

    /**
     * Webhook Handler
     * TODO: عند إضافة المزود: parse الحدث واستدعِ billing->activatePlan() أو cancelPlan()
     */
    public function webhook(Request $request): Response
    {
        // TODO: تحقق من signature المزود ثم عالج الحدث
        return response('OK', 200);
    }
}
