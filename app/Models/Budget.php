<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory, HasUlids, BelongsToUser;

    protected $fillable = [
        'user_id',
        'project_id',
        'category_id',
        'amount',
        'period',
        'month',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'month'  => 'integer',
            'year'   => 'integer',
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

    // ==================== Helpers ====================

    /**
     * المبلغ المنفق فعلياً مقابل هذه الميزانية
     */
    public function spentAmount(): float
    {
        $query = Transaction::expense()
            ->where('user_id', $this->user_id);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        if ($this->project_id) {
            $query->where('project_id', $this->project_id);
        }

        if ($this->period === 'monthly' && $this->month) {
            $query->forMonth($this->month, $this->year);
        } else {
            $query->forYear($this->year);
        }

        return (float) $query->sum('amount');
    }

    /**
     * نسبة الاستهلاك من الميزانية (0-100+)
     */
    public function usagePercentage(): float
    {
        if ($this->amount == 0) return 0;
        return round(($this->spentAmount() / $this->amount) * 100, 1);
    }

    /**
     * المبلغ المتبقي من الميزانية
     */
    public function remainingAmount(): float
    {
        return max(0, $this->amount - $this->spentAmount());
    }

    /**
     * هل تجاوزت الميزانية 80%؟
     */
    public function isNearLimit(): bool
    {
        return $this->usagePercentage() >= 80;
    }

    /**
     * هل تجاوزت الميزانية بالكامل؟
     */
    public function isOverBudget(): bool
    {
        return $this->usagePercentage() > 100;
    }
}
