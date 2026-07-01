<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Modules\CRM\Jobs\RecalculateClientHealthScoreJob;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\TransactionType;
use Carbon\Carbon;

/**
 * InvoicePaymentService — منطق "تسجيل دفع فاتورة" المشترك.
 *
 * استُخرج من InvoiceController::markPaid لإعادة استخدامه من مسارين:
 *   1) التسجيل اليدوي داخل التطبيق (InvoiceController::markPaid) — المستخدم يختار الصندوق.
 *   2) التحصيل التلقائي عبر بوابة الدفع (InvoicePaymentController) — بدون صندوق
 *      (الأموال تبقى لدى دراهم بانتظار التسوية اليدوية مع المشترك).
 *
 * ⚠️ لا تُغيِّر سلوك markPaid الحالي — أي تعديل هنا يؤثر على المسارين معاً.
 */
class InvoicePaymentService
{
    /**
     * يُحدِّث الفاتورة إلى "مدفوعة" وينشئ معاملة دخل مطابقة، ثم يُحدِّث
     * إحصائيات العميل المالية. لا يتحقق من حالة الفاتورة الحالية —
     * على المستدعي التأكد من أنها ليست مدفوعة مسبقاً (idempotency).
     *
     * @param  Invoice     $invoice  يُفضَّل تحميله مع علاقة client مسبقاً
     * @param  string|null $walletId الصندوق الذي أودعت فيه المبالغ — null إذا لم يُحدَّد بعد
     *                                (مثال: تحصيل عبر بوابة دفع بانتظار تسوية يدوية)
     * @param  string      $notes    ملاحظة المعاملة — تختلف بين التسجيل اليدوي والتحصيل التلقائي
     */
    public function markPaid(
        Invoice $invoice,
        ?string $walletId = null,
        string $notes = 'تم الإنشاء تلقائياً عند تسجيل دفع الفاتورة.'
    ): Transaction {
        $paidAt = now();

        $invoice->update([
            'status'  => InvoiceStatus::Paid,
            'paid_at' => $paidAt,
        ]);

        // ── تسجيل معاملة دخل تلقائياً ──────────────────────────────
        $txData = [
            'user_id'          => $invoice->user_id,
            'type'             => TransactionType::Income,
            'amount'           => $invoice->total,
            'currency'         => $invoice->currency,
            'description'      => 'فاتورة ' . $invoice->number
                                  . ($invoice->title ? ' — ' . $invoice->title : ''),
            'payee'            => $invoice->client->name,
            'transaction_date' => $paidAt->toDateString(),
            'reference'        => $invoice->number,
            'wallet_id'        => $walletId,
            'notes'            => $notes,
        ];

        if ($invoice->project_id) {
            $txData['project_id'] = $invoice->project_id;
        }

        $transaction = Transaction::create($txData);

        // ── تحديث إحصائيات العميل (total_paid + last_payment_at) ─────
        $this->refreshClientFinancials($invoice, $paidAt);

        // ── إعادة حساب Health Score للعميل في الخلفية (GAP-02) ─────
        if ($invoice->client_id) {
            RecalculateClientHealthScoreJob::dispatch($invoice->client_id)
                ->onQueue('crm-default')
                ->delay(now()->addSeconds(5)); // تأخير قصير لضمان commit DB أولاً
        }

        return $transaction;
    }

    /**
     * يُعيد حساب total_paid / total_revenue / last_payment_at للعميل
     * بناءً على فواتيره الحالية — نفس منطق markPaid الأصلي تماماً.
     */
    public function refreshClientFinancials(Invoice $invoice, ?Carbon $paidAt = null): void
    {
        if (! $invoice->client_id) {
            return;
        }

        $client = Client::find($invoice->client_id);
        if (! $client) {
            return;
        }

        $paid = Invoice::where('client_id', $client->id)
            ->where('user_id', $invoice->user_id)
            ->where('status', InvoiceStatus::Paid)
            ->sum('total');

        $revenue = Invoice::where('client_id', $client->id)
            ->where('user_id', $invoice->user_id)
            ->whereNotIn('status', [InvoiceStatus::Cancelled])
            ->sum('total');

        $client->update([
            'total_paid'      => $paid,
            'total_revenue'   => $revenue,
            'last_payment_at' => $paidAt ?? now(),
        ]);
    }
}
