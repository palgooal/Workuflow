<?php

namespace App\Models;

use App\Support\Enums\SettlementRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * SettlementRequest — طلب يُنشئه المشترك من صفحة "تحصيلاتي" (/collections)
 * ليطلب من دراهم تحويل صافي التحصيلات الجاهزة (settlement_net_amount) إليه.
 *
 * المشترك يطلب فقط — لا يحدث أي تحويل مال تلقائي عند الإنشاء. الأدمن هو من
 * يعتمد/يرفض الطلب من Filament (SettlementRequestResource)، ثم يُعلِّمه
 * "مدفوع" يدوياً بعد التحويل الفعلي خارج النظام — عندها فقط تتحول
 * PaymentCollection المرتبطة إلى status=settled. راجع docs/PAYMENT-COLLECTION.md.
 */
class SettlementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'currency',
        'status',
        'requested_at',
        'reviewed_at',
        'paid_at',
        'admin_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status'       => SettlementRequestStatus::class,
            'total_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'reviewed_at'  => 'datetime',
            'paid_at'      => 'datetime',
            'metadata'     => 'array',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * التحصيلات (PaymentCollection) التي شملها هذا الطلب وقت إنشائه.
     * belongsToMany عمداً — راجع docblock جدول الـ pivot لسبب عدم استخدام hasMany.
     */
    public function paymentCollections(): BelongsToMany
    {
        return $this->belongsToMany(PaymentCollection::class, 'settlement_request_payment_collection');
    }

    // ==================== Helpers ====================

    public function isPending(): bool
    {
        return $this->status === SettlementRequestStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === SettlementRequestStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === SettlementRequestStatus::Rejected;
    }

    public function isPaid(): bool
    {
        return $this->status === SettlementRequestStatus::Paid;
    }
}
