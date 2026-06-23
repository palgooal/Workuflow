<?php

namespace App\Http\Controllers;

use App\Modules\Billing\Contracts\PaymentProviderInterface;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Billing\Services\TogoPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
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
        $user          = auth()->user();
        $subscription  = $this->billing->getCurrentSubscription($user);
        $planPrices    = $this->billing->getPlanPrices();
        $currentPlan   = $user->currentPlan();
        $providerReady = $this->billing->isPaymentProviderConfigured();

        return view('billing.index', compact(
            'user', 'subscription', 'planPrices', 'currentPlan', 'providerReady'
        ));
    }

    /**
     * بدء عملية الدفع عبر Togo
     * الخطوات 2+3: إنشاء RFP order ثم redirect لصفحة Togo
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => ['required', 'in:pro,business'],
        ]);

        if (! $this->billing->isPaymentProviderConfigured()) {
            return back()->with('info', 'بوابة الدفع غير مفعّلة بعد. تواصل مع الدعم.');
        }

        try {
            $url = app(PaymentProviderInterface::class)
                ->createCheckoutUrl(auth()->user(), $request->plan);

            return redirect()->away($url);
        } catch (\RuntimeException $e) {
            Log::error('Togo checkout error', [
                'user' => auth()->id(),
                'plan' => $request->plan,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Callback بعد نجاح الدفع — Togo يُعيد المستخدم هنا
     * الخطوة 4: التحقق من حالة الطلب قبل تفعيل الخطة
     */
    public function togoCallback(Request $request): RedirectResponse
    {
        $orderId = session('togo_order_id');
        $plan    = session('togo_order_plan');

        if (! $orderId || ! $plan) {
            return redirect()->route('billing.index')
                ->with('error', 'انتهت جلسة الدفع. إذا اكتمل الدفع تواصل مع الدعم.');
        }

        try {
            /** @var TogoPaymentService $togo */
            $togo  = app(TogoPaymentService::class);
            $order = $togo->verifyOrder($orderId);
            $status = $order['status'] ?? 'UNKNOWN';

            if ($status === 'PAID') {
                $this->billing->activatePlan(
                    user: auth()->user(),
                    planValue: $plan,
                    providerSubscriptionId: $orderId,
                );

                session()->forget(['togo_order_id', 'togo_order_plan']);

                Log::info('Togo payment succeeded', [
                    'user'     => auth()->id(),
                    'plan'     => $plan,
                    'order_id' => $orderId,
                ]);

                return redirect()->route('billing.success');
            }

            // الدفع لم يكتمل بعد
            Log::warning('Togo callback: order not PAID', [
                'user'     => auth()->id(),
                'order_id' => $orderId,
                'status'   => $status,
            ]);

            return redirect()->route('billing.upgrade')
                ->with('error', "لم يكتمل الدفع (الحالة: {$status}). تواصل مع الدعم إذا خُصمت المبالغ.");

        } catch (\RuntimeException $e) {
            Log::error('Togo callback error', [
                'user'    => auth()->id(),
                'error'   => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return redirect()->route('billing.upgrade')
                ->with('error', 'حدث خطأ أثناء التحقق من الدفع. تواصل مع الدعم.');
        }
    }

    /**
     * Callback عند إلغاء المستخدم للدفع من صفحة Togo
     */
    public function togoCancel(): RedirectResponse
    {
        session()->forget(['togo_order_id', 'togo_order_plan']);

        return redirect()->route('billing.upgrade')
            ->with('info', 'تم إلغاء عملية الدفع. يمكنك المحاولة مجدداً في أي وقت.');
    }

    /**
     * صفحة نجاح الدفع
     */
    public function success(Request $request): View
    {
        return view('billing.success', [
            'plan' => auth()->user()->currentPlan(),
        ]);
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
     * بوابة إدارة الاشتراك — Togo لا تدعمها
     */
    public function portal(): RedirectResponse
    {
        if (! $this->billing->isPaymentProviderConfigured()) {
            return back()->with('info', 'بوابة الدفع غير مفعّلة بعد.');
        }

        return redirect(
            app(PaymentProviderInterface::class)->createPortalUrl(auth()->user())
        );
    }

    /**
     * Webhook Handler (Stripe فقط — Togo تستخدم redirect callbacks)
     */
    public function webhook(Request $request): Response
    {
        return response('OK', 200);
    }
}
