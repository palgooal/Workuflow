<?php

namespace App\Modules\CRM\Models;

use App\Models\User;
use App\Modules\CRM\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientImportLog extends Model
{
    use HasUlids;

    protected $table = 'client_import_logs';

    protected $fillable = [
        'user_id',
        'filename',
        'idempotency_key',
        'status',
        'total_rows',
        'success_count',
        'error_count',
        'skipped_count',
        'errors',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'        => ImportStatus::class,
            'total_rows'    => 'integer',
            'success_count' => 'integer',
            'error_count'   => 'integer',
            'skipped_count' => 'integer',
            'errors'        => 'array',
            'completed_at'  => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
                     ->orderByDesc('created_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            ImportStatus::Completed->value,
            ImportStatus::Partial->value,
        ]);
    }

    // ==================== Helpers ====================

    public function isProcessing(): bool
    {
        return $this->status === ImportStatus::Processing;
    }

    public function isFinished(): bool
    {
        return $this->status->isTerminal();
    }

    public function successRate(): float
    {
        if ($this->total_rows === 0) return 0;

        return round(($this->success_count / $this->total_rows) * 100, 1);
    }

    /** ملخص النتيجة للعرض */
    public function summary(): string
    {
        return "نجح: {$this->success_count} | فشل: {$this->error_count} | تجاهل: {$this->skipped_count}";
    }
}
