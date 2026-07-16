<?php

namespace App\Console\Commands\DataExport;

use App\Models\ActivityLog;
use App\Models\DataExportRequest;
use App\Support\Enums\DataExportStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * exports:purge-expired — يُجدوَل كل ساعة. يحذف ملفات ZIP الخاصة بتصدير
 * بيانات المستخدمين بعد انتهاء صلاحيتها (افتراضياً 72 ساعة)، ويُعلِّم الطلب
 * كـ expired. راجع docs/DATA-EXPORT.md.
 */
class PurgeExpiredDataExportsCommand extends Command
{
    protected $signature = 'exports:purge-expired';

    protected $description = 'حذف ملفات تصدير بيانات المستخدمين المنتهية الصلاحية';

    public function handle(): int
    {
        $disk = config('backups.user_export.disk');

        $expired = DataExportRequest::query()->dueForExpiry()->get();

        foreach ($expired as $request) {
            try {
                if ($request->file_path && Storage::disk($disk)->exists($request->file_path)) {
                    Storage::disk($disk)->delete($request->file_path);
                }

                $request->update(['status' => DataExportStatus::Expired]);

                ActivityLog::record(
                    eventType: 'export.expired_cleanup',
                    userId: $request->user_id,
                    entityType: DataExportRequest::class,
                    entityId: $request->id,
                );
            } catch (\Throwable $e) {
                $this->warn("تعذّر تنظيف الطلب {$request->id}: {$e->getMessage()}");
            }
        }

        $this->info("تم تنظيف {$expired->count()} طلب تصدير منتهي الصلاحية.");

        return self::SUCCESS;
    }
}
