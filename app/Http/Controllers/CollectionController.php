<?php

namespace App\Http\Controllers;

use App\Models\PaymentCollection;
use App\Models\SettlementRequest;
use App\Support\Enums\PaymentCollectionStatus;
use App\Support\Enums\SettlementRequestStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * واجهة المشترك "تحصيلاتي" — عرض عمليات تحصيل فواتيره عبر بوابة الدفع فقط.
 *
 * صفحة للقراءة فقط: لا تعديل ولا حذف ولا تسوية من هنا — التسوية تبقى حصراً
 * من لوحة إدارة Filament (PaymentCollectionResource). راجع docs/PAYMENT-COLLECTION.md.
 *
 * ⚠️ الملخّص والأرقام هنا تعتمد على settlement_* (بالشيكل دائماً حالياً) وليس
 * على amount/currency (عملة الفاتورة) — Togo تُحصِّل وتُسوِّي فعلياً بالشيكل
 * بغض النظر عن عملة الفاتورة، فعرض net_amount القديم بعملة الفاتورة كان مضلِّلاً.
 */
class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        // القائمة المعروضة (مع فلتر الحالة الاختياري)
        $query = PaymentCollection::where('user_id', $userId)
            ->with(['invoice:id,number,ulid', 'client:id,name'])
            ->latest('collected_at')
            ->latest('id');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $collections = $query->paginate(15)->withQueryString();

        // عملة التسوية المعتمدة للبطاقات — Togo تُسوّي بالشيكل دائماً حالياً.
        // نُثبِّت الفلترة صراحةً على settlement_currency بدل جمع كل شيء أعمى:
        // لو ظهرت مستقبلاً عملة تسوية أخرى لن تُخلَط أبداً بأرقام الشيكل هنا.
        $settlementCurrency = 'ILS';

        // الإحصائيات: دائماً على كامل سجلات المستخدم، بمعزل عن فلتر الجدول
        $base = fn () => PaymentCollection::where('user_id', $userId)
            ->where('settlement_currency', $settlementCurrency);

        $summary = [
            // إجمالي المحصّل للتسوية بالشيكل (status = collected، مبلغ معروف فقط)
            'collected_amount' => (float) $base()
                ->where('status', PaymentCollectionStatus::Collected)
                ->whereNotNull('settlement_amount')
                ->sum('settlement_amount'),
            // الصافي بانتظار التسوية بالشيكل (collected فقط)
            'collected_net'    => (float) $base()
                ->where('status', PaymentCollectionStatus::Collected)
                ->whereNotNull('settlement_net_amount')
                ->sum('settlement_net_amount'),
            // إجمالي العمولة بالشيكل (على كل ما تم تحصيله فعلياً: collected + settled)
            'total_fee'        => (float) $base()
                ->whereIn('status', [PaymentCollectionStatus::Collected, PaymentCollectionStatus::Settled])
                ->sum('settlement_platform_fee'),
            // تمت تسويته معي بالشيكل (status = settled)
            'settled_net'      => (float) $base()
                ->where('status', PaymentCollectionStatus::Settled)
                ->whereNotNull('settlement_net_amount')
                ->sum('settlement_net_amount'),
        ];

        // سجلات تم تحصيلها لكن مبلغ تسويتها بالشيكل غير معروف بعد (فاتورة بعملة
        // أجنبية بانتظار تأكيد الأدمن) — نُعلم المشترك بالسبب بدل ترك الأرقام تبدو ناقصة بصمت.
        $pendingSettlementCount = PaymentCollection::where('user_id', $userId)
            ->where('status', PaymentCollectionStatus::Collected)
            ->whereNull('settlement_amount')
            ->count();

        // ── طلبات التسوية (SettlementRequest) ────────────────────────────
        $settlementRequests = SettlementRequest::where('user_id', $userId)
            ->latest('requested_at')
            ->limit(10)
            ->get();

        $hasPendingSettlementRequest = SettlementRequest::where('user_id', $userId)
            ->where('status', SettlementRequestStatus::Pending)
            ->exists();

        // زر "طلب تسوية" يظهر فقط عند وجود مبالغ جاهزة فعلاً وعدم وجود طلب
        // مفتوح بالفعل (راجع PaymentCollection::scopeEligibleForSettlementRequest).
        $hasEligibleForSettlement = ! $hasPendingSettlementRequest && PaymentCollection::where('user_id', $userId)
            ->eligibleForSettlementRequest()
            ->exists();

        return view('collections.index', compact(
            'collections', 'summary', 'settlementCurrency', 'pendingSettlementCount',
            'settlementRequests', 'hasPendingSettlementRequest', 'hasEligibleForSettlement'
        ));
    }
}
