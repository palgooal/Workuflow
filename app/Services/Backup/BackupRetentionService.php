<?php

namespace App\Services\Backup;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * BackupRetentionService — يطبّق سياسة الاحتفاظ على النسخ الاحتياطية.
 *
 * السياسة (قابلة للتعديل من config/backups.php → system_backup.retention):
 *  - type=database (يومية): الاحتفاظ بآخر N نسخة (افتراضياً 7)، حذف الباقي.
 *  - type=full (كاملة/أسبوعية): نظام Grandfather-Father-Son مبسّط —
 *      • أحدث W نسخة تُحفَظ كنسخ "أسبوعية" (افتراضياً 4).
 *      • بالإضافة، أقدم نسخة كاملة ناجحة في كل شهر من آخر M شهر (افتراضياً 3)
 *        تُحفَظ كنسخة "شهرية"، حتى لو سقطت خارج نطاق الأربع الأسبوعية.
 *      • أي نسخة كاملة لا تقع ضمن أي من المجموعتين تُحذَف.
 *
 * لا تحذف هذه الخدمة إلا النسخ status=completed. النسخ الفاشلة تُترَك لمراجعة
 * الأدمن يدوياً (تُحذَف عبر واجهة Filament مع Confirmation).
 */
class BackupRetentionService
{
    public function apply(): array
    {
        $deleted = [];

        $deleted = array_merge($deleted, $this->applySimpleRetention(
            BackupType::Database,
            (int) config('backups.system_backup.retention.daily', 7)
        ));

        $deleted = array_merge($deleted, $this->applyFullRetention());

        return $deleted;
    }

    /** @return array<int,string> IDs المحذوفة */
    private function applySimpleRetention(BackupType $type, int $keep): array
    {
        $backups = Backup::query()
            ->ofType($type)
            ->successful()
            ->orderByDesc('completed_at')
            ->get();

        $toDelete = $backups->slice($keep);

        return $this->deleteBackups($toDelete);
    }

    /** @return array<int,string> IDs المحذوفة */
    private function applyFullRetention(): array
    {
        $weeklyKeep  = (int) config('backups.system_backup.retention.weekly', 4);
        $monthlyKeep = (int) config('backups.system_backup.retention.monthly', 3);

        $backups = Backup::query()
            ->ofType(BackupType::Full)
            ->successful()
            ->orderByDesc('completed_at')
            ->get();

        $keepIds = collect();

        // أحدث N كنسخ أسبوعية
        $keepIds = $keepIds->merge($backups->take($weeklyKeep)->pluck('id'));

        // أقدم نسخة ناجحة في كل شهر من آخر M شهر كـ "نسخة شهرية"
        $byMonth = $backups->groupBy(fn (Backup $b) => $b->completed_at->format('Y-m'));
        $recentMonths = $byMonth->keys()->sort()->reverse()->take($monthlyKeep);

        foreach ($recentMonths as $month) {
            $oldestInMonth = $byMonth[$month]->sortBy('completed_at')->first();
            if ($oldestInMonth) {
                $keepIds->push($oldestInMonth->id);
            }
        }

        $keepIds = $keepIds->unique();
        $toDelete = $backups->reject(fn (Backup $b) => $keepIds->contains($b->id));

        return $this->deleteBackups($toDelete);
    }

    /** @return array<int,string> */
    private function deleteBackups(iterable $backups): array
    {
        $deletedIds = [];

        foreach ($backups as $backup) {
            /** @var Backup $backup */
            try {
                if ($backup->disk && $backup->path && Storage::disk($backup->disk)->exists($backup->path)) {
                    Storage::disk($backup->disk)->delete($backup->path);
                }

                ActivityLog::record(
                    eventType: 'backup.retention_deleted',
                    userId: null,
                    entityType: Backup::class,
                    entityId: $backup->id,
                    metadata: ['name' => $backup->name, 'type' => $backup->type->value],
                );

                $deletedIds[] = $backup->id;
                $backup->delete();
            } catch (\Throwable $e) {
                Log::warning('BackupRetentionService: فشل حذف نسخة قديمة', [
                    'backup_id' => $backup->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return $deletedIds;
    }
}
