<?php

namespace App\Http\Controllers;

use App\Models\PaymentCollection;
use App\Models\SettlementRequest;
use App\Support\Enums\SettlementRequestStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SettlementRequestController — طلب تسوية من المشترك (صفحة /collections).
 *
 * المشترك يطلب فقط — لا تحويل مال هنا ولا أي مكان آخر تلقائياً. الأدمن هو من
 * يعتمد/يرفض الطلب ثم يُعلِّمه "مدفوع" يدوياً من Filament بعد التحويل الفعلي
 * خارج النظام. راجع docs/PAYMENT-COLLECTION.md قسم "طلبات التسوية".
 */
class SettlementRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        // ── منع تكرار الطلبات: طلب pending واحد فقط في نفس الوقت ───────────
        $hasPending = SettlementRequest::where('user_id', $userId)
            ->where('status', SettlementRequestStatus::Pending)
            ->exists();

        if ($hasPending) {
            return redirect()->route('collections.index')
                ->with('error', 'لديك طلب تسوية قيد المراجعة بالفعل.');
        }

        $settlementRequest = DB::transaction(function () use ($userId) {
            // إعادة الفحص داخل المعاملة (حماية إضافية من نقرتين متزامنتين
            // على الزر قبل أن يُكمِل الفحص الأول التزامه) + قفل الصفوف المؤهَّلة.
            $stillHasPending = SettlementRequest::where('user_id', $userId)
                ->where('status', SettlementRequestStatus::Pending)
                ->lockForUpdate()
                ->exists();

            if ($stillHasPending) {
                return null;
            }

            $eligible = PaymentCollection::where('user_id', $userId)
                ->eligibleForSettlementRequest()
                ->lockForUpdate()
                ->get();

            if ($eligible->isEmpty()) {
                return null;
            }

            $totalAmount = round((float) $eligible->sum('settlement_net_amount'), 2);

            $settlementRequest = SettlementRequest::create([
                'user_id'      => $userId,
                'total_amount' => $totalAmount,
                'currency'     => 'ILS',
                'status'       => SettlementRequestStatus::Pending,
                'requested_at' => now(),
            ]);

            // لا نُغيّر status أو settled_at لأي PaymentCollection هنا — فقط ربط.
            $settlementRequest->paymentCollections()->attach($eligible->pluck('id'));

            Log::info('Subscriber created SettlementRequest', [
                'settlement_request_id' => $settlementRequest->id,
                'user_id'               => $userId,
                'total_amount'          => $totalAmount,
                'collections_count'     => $eligible->count(),
            ]);

            return $settlementRequest;
        });

        if ($settlementRequest === null) {
            return redirect()->route('collections.index')
                ->with('error', 'لا توجد مبالغ جاهزة للتسوية حالياً، أو لديك طلب قيد المراجعة بالفعل.');
        }

        return redirect()->route('collections.index')
            ->with('success', 'تم إرسال طلب التسوية بنجاح، بانتظار مراجعة فريق دراهم.');
    }
}
