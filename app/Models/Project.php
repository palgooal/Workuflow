<?php

namespace App\Models;

use App\Support\Enums\ProjectType;
use App\Support\Enums\TransactionType;
use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, HasUlids, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'client_id',
        'name',
        'description',
        'color',
        'currency',
        'type',
        'is_active',
        'contract_value',
        'expense_budget',
    ];

    protected function casts(): array
    {
        return [
            'type'           => ProjectType::class,
            'is_active'      => 'boolean',
            'contract_value' => 'decimal:2',
            'expense_budget' => 'decimal:2',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'project_service')
            ->using(ProjectServicePivot::class)
            ->withPivot(['id', 'amount', 'type', 'client_id', 'notes'])
            ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBusiness($query)
    {
        return $query->where('type', ProjectType::Business);
    }

    public function scopePersonal($query)
    {
        return $query->where('type', ProjectType::Personal);
    }

    // ==================== Helpers ====================

    public function totalIncome(): float
    {
        return $this->transactions()
            ->where('type', TransactionType::Income)
            ->sum('amount');
    }

    public function totalExpenses(): float
    {
        return $this->transactions()
            ->where('type', TransactionType::Expense)
            ->sum('amount');
    }

    public function netProfit(): float
    {
        return $this->totalIncome() - $this->totalExpenses();
    }
}
