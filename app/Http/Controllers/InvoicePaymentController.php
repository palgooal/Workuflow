<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentCollection;
use App\Models\Setting;
use App\Modules\Billing\Services\TogoPaymentService;
use App\Services\InvoicePaymentService;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\PaymentCollectionStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * InvoicePaymentController — "التحصيل عبر دراهم".
 *
 * تحصيل مبلغ الفاتورة نيابة عن المشترك عبر بوابة الدفع (Togo حالياً)، ثم
 * تسجيلها كمدفوعة بنفس منطق InvoiceController::markPaid (عبر InvoicePaymentService).
 *
 * الأموال المُحصَّلة تبقى لدى دراهم على البوابة (PaymentCollection.status = collected)
 * إلى أن تُسوَّى يدوياً مع المشترك لاحقاً (status = settled). لا payouts تلقائية الآن.
 *
 * ⚠️ Multi-tenancy: كل المسارات هنا عامة عمداً بدون Auth ولا قيد user_id — الوصول
 * يتم فقط عبر ULID الفاتورة (غير قابل للتخمين). هذا هو الاستثناء الوحيد المسموح
 * به للوصول لفاتورة لا تخص المستخدم الحالي. لا تُضِف هنا أي استعلام يفترض
 * auth()->user()، ولا تُزِل الفحص العام هذا من مسارات الفواتير المحمية الأخرى.
 */
class InvoicePaymentController extends Controller
{
    // ==================== صفحة الدفع العامة ====================

    public function show(Invoice $invoice): View
    {
        $invoice->load(['client', 'user', 'items']);

        $latestCollection = $invoice->paymentCollections()->latest()->first();

        return view('invoices.pay', compact('invoice', 'latestCollection'));
    }

    // ==================== بدء الدفع عبر البوابة ====================

    public function checkout(Invoice $invoice): RedirectResponse
    {
        $invoice->load('client', 'user');

        // ── منع الدفع المكرر ────────────────────────────────────────────
        if ($invoice->status === InvoiceStatus::Paid) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('info', 'هذه الفاتورة مدفوعة مسبقاً.');
        }

        if ($invoice->status === InvoiceStatus::Cancelled) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('error', 'هذه الفاتورة ملغاة ولا يمكن دفعها.');
        }

        if (config('billing.provider') !== 'togo') {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('error', 'بوابة الدفع غير مفعّلة بعد. تواصل مع صاحب الفاتورة.');
        }

        $receiverEmail = $invoice->client->email ?: $invoice->user->email;

        if (! $receiverEmail) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('error', 'لا يوجد بريد إلكتروني مسجَّل لإتمام الدفع. تواصل مع صاحب الفاتورة.');
        }

        // ── سجل تحصيل واحد فقط لكل فاتورة (invoice_id فريد على مستوى DB) ──
        // firstOrCreate ليس آمناً بمفرده ضد Race Conditions (نافذة زمنية
        // بين first() و create())، لذا نعتمد على unique index كخط دفاع
        // ثانٍ ونمسك QueryException لو نجح طلبان متزامنان في تجاوز الفحص.
        try {
            $collection = PaymentCollection::firstOrCreate(
                ['invoice_id' => $invoice->id],
                [
                    'user_id'      => $invoice->user_id,
                    'client_id'    => $invoice->client_id,
                    'provider'     => 'togo',
                    'amount'       => $invoice->total,
                    'currency'     => $invoice->currency,
                    'platform_fee' => 0,
                    'net_amount'   => $invoice->total,
                    'status'       => PaymentCollectionStatus::Pending,
                ]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // طلب متزامن آخر أنشأ السجل للتو — أعِد قراءته بدل تكرار الإدراج
            $collection = PaymentCollection::where('invoice_id', $invoice->id)->firstOrFail();
        }

        // حماية إضافية: لو كانت محصَّلة أو مُسوّاة مسبقاً لا نفتح checkout جديداً
        if (in_array($collection->status, [PaymentCollectionStatus::Collected, PaymentCollectionStatus::Settled], true)) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('info', 'هذه الفاتورة مدفوعة مسبقاً.');
        }

        try {
            /** @var TogoPaymentService $togo */
            $togo = app(TogoPaymentService::class);

            $order = $togo->createInvoicePaymentOrder(
                amount:        (float) $invoice->total,
                currency:      $invoice->currency,
                receiverEmail: $receiverEmail,
                successUrl:    route('pay.invoice.callback', $invoice->ulid),
                cancelUrl:     route('pay.invoice.cancel', $invoice->ulid),
            );
        } catch (\Throwable $e) {
            Log::error('InvoicePaymentController::checkout failed', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('error', 'تعذّر بدء عملية الدفع. حاول مجدداً لاحقاً.');
        }

        // نُحدِّث نفس سجل التحصيل بمعطيات المحاولة الجديدة (retry بعد
        // إلغاء/فشل سابق) — invoice_id فريد، فلا يمكن أن يتكدس أكثر من سجل.
        $collection->update([
            'provider'            => 'togo',
            'provider_payment_id' => $order['provider_order_id'],
            'amount'              => (float) $invoice->total,
            'currency'            => $invoice->currency,
            'net_amount'          => max(0, (float) $invoice->total - (float) $collection->platform_fee),
            'status'              => PaymentCollectionStatus::Pending,
            'metadata'            => [
                'provider_hashed_id' => $order['provider_hashed_id'],
                'checkout_url'       => $order['checkout_url'],
                'order_created_raw'  => $order['raw'],
            ],
        ]);

        return redirect()->away($order['checkout_url']);
    }

    // ==================== Callback بعد الدفع (بدون Auth) ====================

    public function callback(Invoice $invoice): RedirectResponse
    {
        $invoice->load('client');

        // فحص سريع قبل أي استدعاء خارجي — قد يعود المستخدم لنفس الرابط أكثر من مرة
        if ($invoice->status === InvoiceStatus::Paid) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('info', 'تم الدفع بنجاح مسبقاً. شكراً لك.');
        }

        // invoice_id فريد على مستوى DB — سجل تحصيل واحد فقط لكل فاتورة
        $collection = PaymentCollection::where('invoice_id', $invoice->id)->first();

        if (! $collection || ! $collection->provider_payment_id) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('error', 'لم يُعثر على عملية دفع نشطة لهذه الفاتورة.');
        }

        if (in_array($collection->status, [PaymentCollectionStatus::Collected, PaymentCollectionStatus::Settled], true)) {
            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('info', 'تم الدفع بنجاح مسبقاً. شكراً لك.');
        }

        try {
            /** @var TogoPaymentService $togo */
            $togo    = app(TogoPaymentService::class);
            $togoRaw = $togo->verifyOrder($collection->provider_payment_id);

            // Live API: {"data": {...}} — Sandbox: {"items": [{...}]}
            if (isset($togoRaw['items'][0]) && is_array($togoRaw['items'][0])) {
                $togoData = $togoRaw['items'][0];
            } elseif (isset($togoRaw['data']) && is_array($togoRaw['data'])) {
                $togoData = $togoRaw['data'];
            } else {
                $togoData = $togoRaw;
            }

            $status       = strtoupper($togoData['status'] ?? 'UNKNOWN');
            $paidStatuses = ['PAID', 'COMPLETED', 'SUCCESS', 'ACCEPTED', 'CONFIRMED'];

            // Sandbox: TO_PAY مع transaction_id يعني أن الدفع انطلق فعلياً من البنك
            if ($status === 'TO_PAY' && ! empty($togoData['transaction_id'])) {
                $status = 'PAID';
            }

            if (! in_array($status, $paidStatuses, true)) {
                $collection->update([
                    'status'   => PaymentCollectionStatus::Failed,
                    'metadata' => array_merge($collection->metadata ?? [], ['callback_raw' => $togoData]),
                ]);

                Log::warning('InvoicePaymentController::callback — payment not in paid statuses', [
                    'invoice_id'    => $invoice->id,
                    'collection_id' => $collection->id,
                    'togo_status'   => $status,
                ]);

                return redirect()->route('pay.invoice.show', $invoice->ulid)
                    ->with('error', 'لم يكتمل الدفع (' . $status . '). حاول مجدداً.');
            }
        } catch (\Throwable $e) {
            Log::error('InvoicePaymentController::callback — verify exception', [
                'invoice_id'    => $invoice->id,
                'collection_id' => $collection->id,
                'error'         => $e->getMessage(),
            ]);

            return redirect()->route('pay.invoice.show', $invoice->ulid)
                ->with('error', 'تعذّر التحقق من حالة الدفع. تواصل مع الدعم إذا خُصم المبلغ.');
        }

        // ── حساب عمولة بوابة الدفع (platform_fee) قبل التسوية ────────────
        // الأولوية دائماً لمبلغ العمولة الفعلي من Togo نفسه إن أرجعته الاستجابة.
        // وإلا: تُقرأ إعدادات العمولة من لوحة الإدارة (جدول settings، group=payment)
        // — PaymentSettings — وليس من .env/config إطلاقاً (راجع docs/PAYMENT-COLLECTION.md).
        // ⚠️ هذا لا يمس invoice.total إطلاقاً، ولا مبلغ Transaction الذي يبقى
        // بقيمة الفاتورة الكاملة (ما دفعه العميل فعلياً) — العمولة تُخصم فقط
        // من صافي المبلغ المستحق للمشترك داخل PaymentCollection.
        $collectedAmount = (float) $collection->amount;
        $platformFee     = app(TogoPaymentService::class)->extractCommissionAmount($togoData);
        $feeRatePercent  = null;
        $fixedFee        = null;

        if ($platformFee !== null) {
            $feeSource = 'togo_response';
        } else {
            $feeEnabled = filter_var(Setting::get('invoice_collection_fee_enabled', true), FILTER_VALIDATE_BOOLEAN);

            if (! $feeEnabled) {
                $platformFee = 0.0;
                $feeSource   = 'disabled';
            } else {
                $feeRatePercent = (float) Setting::get('invoice_collection_fee_rate', 2.5);
                $fixedFee       = (float) Setting::get('invoice_collection_fixed_fee', 0);
                $platformFee    = ($collectedAmount * $feeRatePercent / 100) + $fixedFee;
                $feeSource      = 'admin_settings';
            }
        }

        $platformFee = max(0, round($platformFee, 2));
        $netAmount   = max(0, round($collectedAmount - $platformFee, 2));

        // ── مبلغ التسوية بالشيكل (ILS) — settlement_* ───────────────────────
        // Togo تُحصِّل وتُسوِّي فعلياً بالشيكل دائماً، بغض النظر عن عملة الفاتورة.
        // amount/currency/platform_fee/net_amount أعلاه تبقى بعملة الفاتورة كما
        // هي (audit trail لما "دفعه العميل" اسمياً) ولا تُستخدم للتسوية الفعلية
        // بعد الآن — settlement_* منفصلة تماماً وهي المصدر الوحيد الصحيح لما
        // سيُحوَّل فعلياً للمشترك. راجع docs/PAYMENT-COLLECTION.md.
        $settlementCurrency   = 'ILS';
        $togoSettlementAmount = app(TogoPaymentService::class)->extractSettlementAmount($togoData);
        $togoExchangeRate     = app(TogoPaymentService::class)->extractExchangeRate($togoData);

        if ($togoSettlementAmount !== null) {
            // الأولوية القصوى: مبلغ التسوية الفعلي من رد Togo نفسه.
            $settlementAmount = round($togoSettlementAmount, 2);
            $exchangeRate      = $togoExchangeRate ?? ($collectedAmount > 0 ? round($settlementAmount / $collectedAmount, 6) : null);
            $settlementSource  = 'togo_response';
        } elseif ($togoExchangeRate !== null) {
            // سعر صرف فعلي من Togo بدون مبلغ صريح — نحسب المبلغ منه.
            $exchangeRate      = $togoExchangeRate;
            $settlementAmount = round($collectedAmount * $exchangeRate, 2);
            $settlementSource  = 'togo_response';
        } elseif (strtoupper($invoice->currency) === 'ILS') {
            // الفاتورة بالشيكل أصلاً — لا تحويل مطلوب، لا نفترض أي سعر صرف آخر.
            $exchangeRate      = 1.0;
            $settlementAmount = $collectedAmount;
            $settlementSource  = 'same_currency';
        } else {
            // فاتورة بعملة أجنبية وTogo لم تُرجِع مبلغ/سعر صرف — لا نفترض
            // إطلاقاً (مثلاً لا نساوي USD بـ ILS). يبقى المبلغ مجهولاً حتى
            // يُدخله/يُؤكِّده الأدمن يدوياً من لوحة الإدارة قبل السماح بالتسوية.
            $exchangeRate      = null;
            $settlementAmount = null;
            $settlementSource  = 'pending_admin_review';

            Log::warning('InvoicePaymentController::callback — settlement amount (ILS) unknown for non-ILS invoice', [
                'invoice_id'       => $invoice->id,
                'collection_id'    => $collection->id,
                'invoice_currency' => $invoice->currency,
                'invoice_amount'   => $collectedAmount,
            ]);
        }

        $settlementFeeRatePercent = null;
        $settlementFixedFee       = null;

        if ($settlementAmount !== null) {
            $settlementFeeEnabled = filter_var(Setting::get('invoice_collection_fee_enabled', true), FILTER_VALIDATE_BOOLEAN);

            if (! $settlementFeeEnabled) {
                $settlementPlatformFee = 0.0;
            } else {
                $settlementFeeRatePercent = (float) Setting::get('invoice_collection_fee_rate', 2.5);
                $settlementFixedFee       = (float) Setting::get('invoice_collection_fixed_fee', 0);
                $settlementPlatformFee    = ($settlementAmount * $settlementFeeRatePercent / 100) + $settlementFixedFee;
            }

            $settlementPlatformFee = max(0, round($settlementPlatformFee, 2));
            $settlementNetAmount   = max(0, round($settlementAmount - $settlementPlatformFee, 2));
        } else {
            // settlement_amount مجهول → لا تُحسب أي عمولة أو صافي؛ status يبقى
            // collected لكن settlement_net_amount يبقى null (يمنع التسوية).
            $settlementPlatformFee = 0.0;
            $settlementNetAmount   = null;
        }

        // ── نجح الدفع لدى Togo: نُقفل صف الفاتورة ونُعيد فحص حالتها قبل ─────
        // التنفيذ — هذا يمنع Race Condition لو وصل طلبا callback متزامنان
        // لنفس الفاتورة (مثلاً المستخدم فتح رابط العودة في تبويبين). لا
        // نضع استدعاء Togo الخارجي (verifyOrder أعلاه) داخل القفل عمداً —
        // إبقاء I/O الشبكي خارج معاملة قافلة لصف قد يُعطِّل طلبات أخرى.
        DB::transaction(function () use (
            $invoice, $collection, $togoData,
            $platformFee, $netAmount, $feeSource, $feeRatePercent, $fixedFee,
            $settlementCurrency, $settlementAmount, $settlementPlatformFee, $settlementNetAmount,
            $exchangeRate, $settlementSource, $settlementFeeRatePercent, $settlementFixedFee,
        ) {
            $lockedInvoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            // فحص ثانٍ داخل القفل — خط الدفاع الحقيقي ضد التنفيذ المزدوج
            if ($lockedInvoice->status === InvoiceStatus::Paid) {
                return;
            }

            // العميل غير عرضة لهذا الـ race؛ نُعيد استخدام النسخة المحمَّلة
            // مسبقاً بدل استعلام إضافي.
            $lockedInvoice->setRelation('client', $invoice->client);

            // invoice.total و Transaction.amount لا يتأثران بالعمولة —
            // markPaid() تستخدم invoice->total كما هو (قيمة الفاتورة الكاملة).
            app(InvoicePaymentService::class)->markPaid(
                invoice: $lockedInvoice,
                walletId: null, // لا صندوق بعد — الأموال لدى دراهم بانتظار التسوية اليدوية
                notes: 'تم التحصيل تلقائياً عبر بوابة الدفع (دراهم) — بانتظار التسوية مع المشترك.',
            );

            $collection->update([
                'status'                   => PaymentCollectionStatus::Collected,
                'collected_at'             => now(),
                'platform_fee'             => $platformFee,
                'net_amount'               => $netAmount,
                'settlement_currency'      => $settlementCurrency,
                'settlement_amount'        => $settlementAmount,
                'settlement_platform_fee'  => $settlementPlatformFee,
                'settlement_net_amount'    => $settlementNetAmount,
                'exchange_rate'            => $exchangeRate,
                'metadata'                 => array_merge($collection->metadata ?? [], [
                    'callback_raw'          => $togoData,
                    'platform_fee_source'   => $feeSource,
                    'fee_rate'              => $feeRatePercent,
                    'fixed_fee'             => $fixedFee,
                    'settlement_source'     => $settlementSource,
                    'settlement_fee_rate'   => $settlementFeeRatePercent,
                    'settlement_fixed_fee'  => $settlementFixedFee,
                ]),
            ]);
        });

        Log::info('InvoicePaymentController::callback — invoice collected via gateway', [
            'invoice_id'             => $invoice->id,
            'collection_id'          => $collection->id,
            'platform_fee'           => $platformFee,
            'net_amount'             => $netAmount,
            'fee_source'             => $feeSource,
            'fee_rate'               => $feeRatePercent,
            'fixed_fee'              => $fixedFee,
            'settlement_amount'      => $settlementAmount,
            'settlement_platform_fee'=> $settlementPlatformFee,
            'settlement_net_amount'  => $settlementNetAmount,
            'exchange_rate'          => $exchangeRate,
            'settlement_source'      => $settlementSource,
        ]);

        return redirect()->route('pay.invoice.show', $invoice->ulid)
            ->with('success', '✅ تم الدفع بنجاح. شكراً لك.');
    }

    // ==================== إلغاء الدفع من صفحة البوابة (بدون Auth) ====================

    public function cancel(Invoice $invoice): RedirectResponse
    {
        // لا نُغيّر حالة PaymentCollection هنا — تبقى pending وتُحدَّث في
        // محاولة الدفع التالية عبر checkout() (نفس السجل — invoice_id فريد).
        return redirect()->route('pay.invoice.show', $invoice->ulid)
            ->with('info', 'تم إلغاء عملية الدفع. يمكنك المحاولة مجدداً في أي وقت.');
    }
}
