<?php

namespace App\Models;

use App\Support\Enums\TransactionType;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, HasUlids, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'project_id',
        'category_id',
        'type',
        'amount',
        'currency',
        'description',
        'payee',
        'notes',
        'transaction_date',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'type'             => TransactionType::class,
            'amount'           => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ==================== Scopes ====================

    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense);
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereMonth('transaction_date', $month)
                     ->whereYear('transaction_date', $year);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('transaction_date', $year);
    }

    public function scopeDateBetween($query, string $from, string $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    // ==================== Helpers ====================

    public function isIncome(): bool
    {
        return $this->type === TransactionType::Income;
    }

    public function isExpense(): bool
    {
        return $this->type === TransactionType::Expense;
    }
}
