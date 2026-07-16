<?php

namespace App\Models;

use App\Support\Enums\DataExportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DataExportRequest — طلب "تنزيل نسخة من بياناتي" (لوحة حساب المستخدم).
 *
 * ⚠️ عمداً لا يستخدم BelongsToUser trait: هذا الموديل يُستعلَم عنه أحياناً من
 * سياقات نظامية (Job التنظيف، الأدمن) حيث لا يوجد auth() نشط بنفس هوية المالك.
 * كل استعلام في الكود المواجه للمستخدم (Controller) يجب أن يستخدم صراحةً
 * where('user_id', auth()->id()) — راجع docs/DATA-EXPORT.md.
 */
class DataExportRequest extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'status',
        'file_path',
        'file_size',
        'requested_at',
        'completed_at',
        'expires_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'       => DataExportStatus::class,
            'file_size'    => 'integer',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    // ==================== Relations ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    /** طلبات ما زالت نشطة (لم تنتهِ بعد) — pending أو processing */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            DataExportStatus::Pending->value,
            DataExportStatus::Processing->value,
        ]);
    }

    /** طلبات مكتملة تجاوز ملفها تاريخ انتهاء الصلاحية ولم تُعلَّم كمنتهية بعد */
    public function scopeDueForExpiry($query)
    {
        return $query->where('status', DataExportStatus::Completed->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    // ==================== Helpers ====================

    public function isDownloadable(): bool
    {
        return $this->status === DataExportStatus::Completed
            && $this->file_path
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    public function humanFileSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = (float) $this->file_size;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1).' '.$units[$i];
    }
}
