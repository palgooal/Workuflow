<?php

namespace App\Models;

use App\Support\Enums\WalletType;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasUlids, BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'initial_balance',
        'color',
        'icon',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'            => WalletType::class,
            'initial_balance' => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(WalletTransfer::class, 'from_wallet_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(WalletTransfer::class, 'to_wallet_id');
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==================== Business Logic ====================

    /**
     * حساب الرصيد الحالي للصندوق
     * الرصيد = الرصيد الافتراضي + الدخل - المصروفات - رسوم التحويلات الصادرة
     */
    public function balance(): float
    {
        $income   = $this->transactions()->income()->sum('amount');
        $expenses = $this->transactions()->expense()->sum('amount');
        $feesOut  = $this->transfersOut()->sum('fee');

        // التحويلات الصادرة تُخصم، التحويلات الواردة تُضاف
        $transfersOut = $this->transfersOut()->sum('amount');
        $transfersIn  = $this->transfersIn()->sum('amount');

        return (float) $this->initial_balance
            + $income
            - $expenses
            + $transfersIn
            - $transfersOut
            - $feesOut;
    }

    /**
     * إجمالي الدخل
     */
    public function totalIncome(): float
    {
        return (float) $this->transactions()->income()->sum('amount');
    }

    /**
     * إجمالي المصروفات
     */
    public function totalExpenses(): float
    {
        return (float) $this->transactions()->expense()->sum('amount');
    }
}
