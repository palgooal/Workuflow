<?php

namespace App\Models;

use App\Support\Enums\PaymentCollectionStatus;
use App\Support\Enums\SettlementRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * PaymentCollection — عمليات التحصيل عبر بوابة الدفع نيابة عن المشتركين.
 *
 * عندما يدفع عميل فاتورةً عبر رابط الدفع العام (/pay/invoice/{ulid})، يُحصَّل
 * المبلغ في حساب دراهم على بوابة الدفع (وليس مباشرة في صندوق المشترك)، ثم
 * تُسوَّى الأموال مع المشترك يدوياً لاحقاً (status = settled).
 *
 * لا تُنفَّذ أي payouts تلقائية حالياً — راجع docs/PAYMENT-COLLECTION.md
 *
 * ⚠️ عملة الفاتورة ≠ عملة التسوية: `amount`/`currency` يمثّلان قيمة الفاتورة
 * كما أنشأها المستقل (لا تتغيّر أبداً). لكن بوابة الدفع (Togo) تُحصِّل وتُسوِّي
 * فعلياً بالشيكل (ILS) دائماً — لذلك `settlement_amount`/`settlement_currency`/
 * `settlement_platform_fee`/`settlement_net_amount` هي المصدر الوحيد الصحيح
 * لما سيُحوَّل فعلياً للمشترك، وقد تكون `settlement_amount` = null مؤقتاً
 * لفواتير بعملة غير الشيكل بانتظار تأكيد الأدمن (راجع isSettlementAmountKnown()).
 */
class PaymentCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'client_id',
        'provider',
        'provider_payment_id',
        'amount',
        'currency',
        'platform_fee',
        'net_amount',
        'settlement_currency',
        'settlement_amount',
        'settlement_platform_fee',
        'settlement_net_amount',
        'exchange_rate',
        'status',
        'collected_at',
        'settled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status'                   => PaymentCollectionStatus::class,
            'amount'                   => 'decimal:2',
            'platform_fee'             => 'decimal:2',
            'net_amount'               => 'decimal:2',
            'settlement_amount'        => 'decimal:2',
            'settlement_platform_fee'  => 'decimal:2',
            'settlement_net_amount'    => 'decimal:2',
            'exchange_rate'            => 'decimal:6',
            'collected_at'             => 'datetime',
            'settled_at'               => 'datetime',
            'metadata'                 => 'array',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * طلبات التسوية (SettlementRequest) التي شملت هذا التحصيل عبر الزمن —
     * قد يكون أكثر من واحد (مثلاً إن رُفض طلب أول ثم أُدرِج التحصيل في طلب لاحق).
     */
    public function settlementRequests(): BelongsToMany
    {
        return $this->belongsToMany(SettlementRequest::class, 'settlement_request_payment_collection');
    }

    // ==================== Scopes ====================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', PaymentCollectionStatus::Pending);
    }

    public function scopeCollected($query)
    {
        return $query->where('status', PaymentCollectionStatus::Collected);
    }

    public function scopeSettled($query)
    {
        return $query->where('status', PaymentCollectionStatus::Settled);
    }

    /**
     * مؤهَّل لإدراجه في طلب تسوية جديد (SettlementRequest) من المشترك:
     * تم تحصيله فعلياً + مبلغ صافي التسوية بالشيكل معروف + غير مرتبط حالياً
     * بأي طلب "مفتوح" (pending أو approved) — لمنع احتساب نفس المبلغ مرتين
     * ضمن طلبين متزامنين. راجع SettlementRequestController للاستخدام.
     */
    public function scopeEligibleForSettlementRequest($query)
    {
        return $query
            ->where('status', PaymentCollectionStatus::Collected)
            ->whereNotNull('settlement_net_amount')
            ->whereDoesntHave('settlementRequests', function ($q) {
                $q->whereIn('status', [SettlementRequestStatus::Pending, SettlementRequestStatus::Approved]);
            });
    }

    // ==================== Helpers ====================

    public function isCollected(): bool
    {
        return $this->status === PaymentCollectionStatus::Collected;
    }

    public function isSettled(): bool
    {
        return $this->status === PaymentCollectionStatus::Settled;
    }

    /**
     * يُحدِّث net_amount تلقائياً بناءً على amount و platform_fee الحاليين.
     *
     * ⚠️ هذا الحقل (وnet_amount/platform_fee عموماً) بعملة الفاتورة — للتوافق
     * الخلفي فقط. لا تعتمد عليه لعرض ما سيُحوَّل فعلياً للمشترك؛ استخدم
     * settlement_net_amount (بالشيكل دائماً) بدلاً منه.
     */
    public function recalculateNetAmount(): void
    {
        $this->net_amount = max(0, (float) $this->amount - (float) $this->platform_fee);
    }

    /**
     * هل مبلغ التسوية بالشيكل معروف؟ يكون false لفواتير بعملة غير الشيكل
     * لم تُرجِع بوابة الدفع مبلغ/سعر صرف لها، وتنتظر تحديداً يدوياً من الأدمن.
     */
    public function isSettlementAmountKnown(): bool
    {
        return $this->settlement_amount !== null;
    }

    /**
     * جاهز للتسوية مع المشترك: تم التحصيل فعلياً + مبلغ التسوية بالشيكل معروف.
     */
    public function isReadyForSettlement(): bool
    {
        return $this->status === PaymentCollectionStatus::Collected && $this->isSettlementAmountKnown();
    }

    /**
     * هل هذا التحصيل مرتبط حالياً بطلب تسوية (SettlementRequest) لا يزال
     * "مفتوحاً" (pending أو approved)؟
     *
     * ⚠️ هذا الفحص ضروري لمنع الأدمن من استخدام الزر المستقل "تسوية مع
     * المشترك" (PaymentCollectionResource) على تحصيل يخضع بالفعل لطلب تسوية
     * قيد المراجعة/معتمد — لأن ذلك يُحوِّل status→settled مباشرة دون تحديث
     * SettlementRequest المرتبط، فيبقى الطلب عالقاً على "قيد المراجعة" للأبد
     * رغم أن كل تحصيلاته أصبحت settled. المسار الصحيح في هذه الحالة هو
     * "تعليم كمدفوع" من SettlementRequestResource فقط.
     */
    public function hasOpenSettlementRequest(): bool
    {
        return $this->settlementRequests()
            ->whereIn('status', [SettlementRequestStatus::Pending, SettlementRequestStatus::Approved])
            ->exists();
    }

    /**
     * يُحدِّث settlement_net_amount تلقائياً بناءً على settlement_amount
     * و settlement_platform_fee الحاليين. لا تُغيّر شيئاً إن كان
     * settlement_amount غير معروف بعد (null).
     */
    public function recalculateSettlementNetAmount(): void
    {
        if ($this->settlement_amount === null) {
            $this->settlement_net_amount = null;
            return;
        }

        $this->settlement_net_amount = max(0, (float) $this->settlement_amount - (float) $this->settlement_platform_fee);
    }
}
