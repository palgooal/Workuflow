<?php

namespace App\Http\Controllers;

use App\Events\PaymentSucceeded;
use App\Events\PaymentFailed as PaymentFailedEvent;
use App\Models\FailedPaymentCallback;
use App\Models\PaymentOrder;
use App\Models\Subscription;
use App\Modules\Billing\Contracts\PaymentProviderInterface;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Billing\Services\TogoPaymentService;
use App\Notifications\PaymentSuccessfulNotification;
use App\Notifications\PaymentFailedNotification;
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
            'plan'  => ['required', 'in:pro,business'],
            'cycle' => ['sometimes', 'in:monthly,annual'],
        ]);

        $cycle = $request->input('cycle', 'monthly');

        if (! $this->billing->isPaymentProviderConfigured()) {
            return back()->with('info', 'بوابة الدفع غير مفعّلة بعد. تواصل مع الدعم.');
        }

        try {
            // createCheckoutUrl ينشئ PaymentOrder ويخزّن checkout_url في metadata
            // لا نحتاج الـ URL هنا — سيقرأه togoPending() من PaymentOrder مباشرة
            app(PaymentProviderInterface::class)
                ->createCheckoutUrl(auth()->user(), $request->plan, $cycle);

            // أرسل المستخدم لصفحة تأكيد ما قبل الدفع (Darahum-branded)
            return redirect()->route('billing.togo.pending');
        } catch (\RuntimeException $e) {
            Log::error('Togo checkout error', [
                'user'  => auth()->id(),
                'plan'  => $request->plan,
                'cycle' => $cycle,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * صفحة تأكيد ما قبل الدفع — تُعرض بين checkout وبوابة Togo.
     *
     * تعرض ملخص الطلب (خطة، دورة، مبلغ) وزر "متابعة إلى الدفع"
     * الذي يأخذ المستخدم مباشرة لـ checkout_url المخزّن في PaymentOrder.
     */
    public function togoPending(): View|RedirectResponse
    {
        $paymentOrderId = session('payment_order_id');

        if (! $paymentOrderId) {
            return redirect()->route('billing.index')
                ->with('info', 'لم يُعثر على جلسة دفع نشطة. اختر خطتك وابدأ من جديد.');
        }

        $order = PaymentOrder::where('id', $paymentOrderId)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (! $order) {
            return redirect()->route('billing.index')
                ->with('info', 'انتهت جلسة الدفع أو اكتملت. اختر خطتك للمتابعة.');
        }

        $checkoutUrl = $order->metadata['checkout_url'] ?? null;

        if (! $checkoutUrl) {
            Log::error('togoPending: checkout_url missing from PaymentOrder metadata', [
                'order_id' => $order->id,
                'user'     => auth()->id(),
            ]);
            return redirect()->route('billing.index')
                ->with('error', 'تعذّر استرداد رابط الدفع. حاول مجدداً.');
        }

        $planLabels = [
            'pro'      => 'Pro ⚡',
            'business' => 'Business 🚀',
        ];
        $cycleLabels = [
            'monthly' => 'شهري',
            'annual'  => 'سنوي (12 شهراً)',
        ];

        // تسجيل لحظة توجيه المستخدم لبوابة Togo
        $order->addTimelineEvent('user.redirected_to_gateway', ['gateway' => 'togo']);

        return view('billing.togo-pending', [
            'order'       => $order,
            'checkoutUrl' => $checkoutUrl,
            'planLabel'   => $planLabels[$order->plan]  ?? $order->plan,
            'cycleLabel'  => $cycleLabels[$order->cycle] ?? $order->cycle,
        ]);
    }

    /**
     * Callback بعد نجاح الدفع — Togo يُعيد المستخدم هنا
     *
     * ⚠️  هذا الـ route بدون Auth middleware عمداً:
     *     Session قد تنتهي أثناء الدفع على صفحة Togo.
     *     المستخدم يُجلب من PaymentOrder مباشرة.
     */
    public function togoCallback(Request $request): RedirectResponse
    {
        /** @var TogoPaymentService $togo */
        $togo = app(TogoPaymentService::class);

        // ── 1. تحميل PaymentOrder من DB (بدون قيد auth) ─────────────────
        $paymentOrder = $this->resolvePaymentOrderGuest($request);

        if (! $paymentOrder) {
            Log::warning('Togo callback: PaymentOrder not found', [
                'session_order_id' => session('payment_order_id'),
                'query'            => $request->query(),
            ]);

            return redirect()->route('billing.index')
                ->with('error', 'انتهت جلسة الدفع أو لم يُعثر على الطلب. إذا اكتمل الدفع تواصل مع الدعم.');
        }

        /** @var \App\Models\User $user */
        $user = $paymentOrder->user;

        // ── 2. Idempotency guard ─────────────────────────────────────────
        if ($paymentOrder->isPaid()) {
            Log::info('Togo callback: already processed (idempotent)', [
                'order_id' => $paymentOrder->id,
                'user'     => $user->id,
            ]);

            return redirect()->route('billing.success');
        }

        // ── 3. التحقق من حالة الطلب عبر Togo API ───────────────────────
        try {
            $togoRaw  = $togo->verifyOrder($paymentOrder->provider_order_id);

            // verifyOrder قد تُرجع items[] wrapper أو items[0] مباشرةً — نتعامل مع الحالتين
            if (isset($togoRaw['items'][0]) && is_array($togoRaw['items'][0])) {
                $togoData = $togoRaw['items'][0];
            } elseif (isset($togoRaw['data']) && is_array($togoRaw['data'])) {
                $togoData = $togoRaw['data'];
            } else {
                $togoData = $togoRaw; // already parsed items[0]
            }

            $status = $togoData['status'] ?? 'UNKNOWN';

            Log::info('Togo callback: verifyOrder response', [
                'payment_order_id'  => $paymentOrder->id,
                'provider_order_id' => $paymentOrder->provider_order_id,
                'togo_status'       => $status,
                'togo_data'         => $togoData,
                'user'              => $user->id,
            ]);

            // DEBUG مؤقت — يُحذف بعد حل المشكلة
            session(['togo_debug_response' => $togoData]);

            // Togo Sandbox قد يُرجع status مختلف — نتعامل مع كل قيم النجاح الممكنة
            $paidStatuses = ['PAID', 'COMPLETED', 'SUCCESS', 'ACCEPTED', 'CONFIRMED'];

            // Sandbox: إذا كان TO_PAY وعنده transaction_id → الدفع انطلق من البنك
            if ($status === 'TO_PAY' && ! empty($togoData['transaction_id'])) {
                $status = 'PAID';
            }

            if (in_array(strtoupper($status), $paidStatuses, true)) {
                // ── 4a. تفعيل الاشتراك ──────────────────────────────────
                $paymentOrder->addTimelineEvent('callback.received', ['togo_status' => $status]);
                $paymentOrder->markAsPaid($togoData);
                $paymentOrder->addTimelineEvent('payment.marked_paid');

                $cycle             = $paymentOrder->cycle ?? 'monthly';
                $isFirstActivation = ! Subscription::where('user_id', $user->id)->exists();

                $subscription = $this->billing->activatePlan(
                    user: $user,
                    planValue: $paymentOrder->plan,
                    providerSubscriptionId: $paymentOrder->provider_order_id,
                    cycle: $cycle,
                );
                $paymentOrder->addTimelineEvent('subscription.activated', ['plan' => $paymentOrder->plan, 'cycle' => $cycle]);

                event(new SubscriptionActivated(
                    subscription:      $subscription,
                    isFirstActivation: $isFirstActivation,
                    triggerSource:     'togo_callback',
                    cycle:             $cycle,
                ));

                session()->forget('payment_order_id');

                event(new PaymentSucceeded($paymentOrder));
                $user->notify(new PaymentSuccessfulNotification($paymentOrder));
                $paymentOrder->addTimelineEvent('notification.sent', ['type' => 'PaymentSuccessfulNotification']);

                Log::info('Togo payment succeeded — subscription activated', [
                    'payment_order_id'  => $paymentOrder->id,
                    'provider_order_id' => $paymentOrder->provider_order_id,
                    'plan'              => $paymentOrder->plan,
                    'togo_status'       => $status,
                    'user'              => $user->id,
                ]);

                return redirect()->route('billing.success');
            }

            // ── 4b. الدفع غير مكتمل ─────────────────────────────────────
            $paymentOrder->markAsFailed($togoData);

            event(new PaymentFailedEvent($paymentOrder, "Togo status: {$status}"));
            $user->notify(new PaymentFailedNotification($paymentOrder, "Togo status: {$status}"));

            Log::warning('Togo callback: payment not in paid statuses', [
                'payment_order_id' => $paymentOrder->id,
                'togo_status'      => $status,
                'togo_data'        => $togoData,
                'user'             => $user->id,
            ]);

            session()->forget('payment_order_id');

            return redirect()->route('billing.failed')
                ->with('togo_status', $status);

        } catch (\Throwable $e) {
            Log::error('Togo callback: verifyOrder exception', [
                'payment_order_id'  => $paymentOrder->id,
                'provider_order_id' => $paymentOrder->provider_order_id,
                'error'             => $e->getMessage(),
                'user'              => $user->id,
            ]);

            FailedPaymentCallback::record(
                provider: 'togo',
                orderId:  $paymentOrder->provider_order_id,
                payload:  $request->all(),
                exception: $e,
            );

            return redirect()->route('billing.failed')
                ->with('error', 'حدث خطأ أثناء التحقق من الدفع. تواصل مع الدعم.');
        }
    }

    /**
     * Callback عند إلغاء المستخدم للدفع من صفحة Togo (بدون Auth)
     */
    public function togoCancel(): RedirectResponse
    {
        $paymentOrderId = session()->pull('payment_order_id');

        if ($paymentOrderId) {
            PaymentOrder::where('id', $paymentOrderId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            Log::info('Togo payment cancelled by user', [
                'payment_order_id' => $paymentOrderId,
            ]);
        }

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
     * صفحة فشل الدفع
     */
    public function failed(Request $request): View
    {
        return view('billing.failed', [
            'togoStatus' => session('togo_status'),
        ]);
    }

    /**
     * تحميل PaymentOrder بدون auth (للـ callback).
     *
     * الأولوية 1: session payment_order_id
     * الأولوية 2: orderId (hashed_id) من query string — يُرسله Togo تلقائياً
     */
    private function resolvePaymentOrderGuest(Request $request): ?PaymentOrder
    {
        $togoOrderId = $request->query('orderId');

        // الأولوية 1: session (قد تكون منتهية)
        if ($localId = session('payment_order_id')) {
            $order = PaymentOrder::with('user')->where('id', $localId)->first();
            if ($order) return $order;
        }

        // الأولوية 2: hashed_id من query string
        if ($togoOrderId) {
            return PaymentOrder::with('user')
                ->where('provider_hashed_id', $togoOrderId)
                ->latest()
                ->first();
        }

        return null;
    }

    /**
     * @deprecated استخدم resolvePaymentOrderGuest للـ callback
     */
    private function resolvePaymentOrder(Request $request, TogoPaymentService $togo): ?PaymentOrder
    {
        return $this->resolvePaymentOrderGuest($request);
    }

    /**
     * صفحة الترقية — تواصل معنا أو ادفع الآن
     */
    public function upgrade(): View
    {
        $currentPlan   = auth()->user()->currentPlan();
        $ownerWhatsapp = config('billing.owner_whatsapp');
        $planPrices    = $this->billing->getPlanPrices();
        $providerReady = $this->billing->isPaymentProviderConfigured();

        // ── Plan Intent: قراءة واستهلاك النية المحفوظة من صفحة التسجيل ──
        // pull() تقرأ القيمة وتحذفها من الـ session في نفس الوقت (تُستهلك مرة واحدة).
        // القيمة: ['plan' => 'pro'|'business', 'cycle' => 'monthly'|'annual'] | null
        $paidIntent = session()->pull('paid_intent');

        return view('billing.upgrade', compact(
            'currentPlan', 'ownerWhatsapp', 'planPrices', 'providerReady', 'paidIntent'
        ));
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
