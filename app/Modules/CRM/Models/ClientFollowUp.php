<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use App\Modules\CRM\Enums\FollowUpStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFollowUp extends Model
{
    use HasUlids;

    protected $table = 'client_follow_ups';

    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'title',
        'status',
        'due_at',
        'completed_at',
        'reminder_at',
        'priority',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'       => FollowUpStatus::class,
            'due_at'       => 'datetime',
            'completed_at' => 'datetime',
            'reminder_at'  => 'datetime',
            'priority'     => 'integer',
        ];
    }

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==================== Scopes ====================

    public function scopePending($query)
    {
        return $query->where('status', FollowUpStatus::Pending->value);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', FollowUpStatus::Pending->value)
                     ->where('due_at', '<', now());
    }

    public function scopeDueToday($query)
    {
        return $query->where('status', FollowUpStatus::Pending->value)
                     ->whereDate('due_at', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->where('status', FollowUpStatus::Pending->value)
                     ->whereBetween('due_at', [now(), now()->endOfWeek()]);
    }

    public function scopeWithReminder($query)
    {
        return $query->whereNotNull('reminder_at')
                     ->where('reminder_at', '<=', now())
                     ->where('status', FollowUpStatus::Pending->value);
    }

    // ==================== Helpers ====================

    public function isOverdue(): bool
    {
        return $this->status === FollowUpStatus::Pending
            && $this->due_at->isPast();
    }

    public function actualStatus(): FollowUpStatus
    {
        return FollowUpStatus::resolveActual($this->status, $this->due_at);
    }

    public function daysUntilDue(): int
    {
        return (int) now()->diffInDays($this->due_at, false);
    }
}
