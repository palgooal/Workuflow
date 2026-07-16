<?php

namespace App\Models;

use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Backup — سجل نسخة احتياطية تشغيلية للنظام كاملاً (super_admin فقط عبر Filament).
 *
 * لا علاقة له بـ DataExportRequest (تصدير بيانات مستخدم واحد). هذا يغطي قاعدة
 * البيانات كاملة و/أو ملفات storage الضرورية لكل المستخدمين. راجع docs/BACKUP-SYSTEM.md.
 */
class Backup extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'type',
        'status',
        'triggered_by_user_id',
        'disk',
        'path',
        'size_bytes',
        'checksum',
        'encrypted',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_message',
        'integrity_verified',
        'integrity_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'type'                  => BackupType::class,
            'status'                => BackupStatus::class,
            'encrypted'             => 'boolean',
            'size_bytes'            => 'integer',
            'duration_seconds'      => 'integer',
            'started_at'            => 'datetime',
            'completed_at'          => 'datetime',
            'integrity_verified'    => 'boolean',
            'integrity_checked_at'  => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    // ==================== Scopes ====================

    public function scopeSuccessful($query)
    {
        return $query->where('status', BackupStatus::Completed->value);
    }

    public function scopeFailedOnes($query)
    {
        return $query->where('status', BackupStatus::Failed->value);
    }

    public function scopeOfType($query, BackupType $type)
    {
        return $query->where('type', $type->value);
    }

    // ==================== State transitions ====================

    public function markRunning(): void
    {
        $this->update([
            'status'     => BackupStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function markCompleted(string $disk, string $path, int $sizeBytes, string $checksum, bool $encrypted): void
    {
        $this->update([
            'status'           => BackupStatus::Completed,
            'disk'             => $disk,
            'path'             => $path,
            'size_bytes'       => $sizeBytes,
            'checksum'         => $checksum,
            'encrypted'        => $encrypted,
            'completed_at'     => now(),
            'duration_seconds' => $this->calculateDurationSeconds(),
        ]);
    }

    public function markFailed(string $reason): void
    {
        $this->update([
            'status'           => BackupStatus::Failed,
            'error_message'    => $reason,
            'completed_at'     => now(),
            'duration_seconds' => $this->calculateDurationSeconds(),
        ]);
    }

    /**
     * مدة التشغيل بالثواني من started_at إلى الآن — دائماً عدد صحيح غير سالب.
     *
     * ⚠️ لا تستخدم now()->diffInSeconds($this->started_at) مباشرة: بعض إصدارات
     * Carbon (3.x المستخدَم مع Laravel 12) لا تُرجع القيمة absolute افتراضياً في
     * diffInSeconds()، فيعتمد الناتج على ترتيب الطرفين ويمكن أن يخرج بقيمة سالبة
     * (مثال فعلي واجهناه: -1.036742) — وبما أن عمود duration_seconds من نوع
     * unsignedInteger، هذا يسبب SQLSTATE[22003] Out of range ويفشل التحديث بالكامل
     * (بما فيه markFailed() نفسها). absolute: true + max(0, ...) يمنعان هذا نهائياً
     * بغض النظر عن إصدار Carbon أو ترتيب الاستدعاء.
     */
    private function calculateDurationSeconds(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        return max(0, (int) $this->started_at->diffInSeconds(now(), absolute: true));
    }

    // ==================== Helpers ====================

    public function humanSize(): ?string
    {
        if (! $this->size_bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = (float) $this->size_bytes;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1).' '.$units[$i];
    }
}
