<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int  $sort    = 6;
    protected ?string      $heading = 'صحة النظام';

    // يتحدث كل 30 ثانية
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            $this->queueStat(),
            $this->failedJobsStat(),
            $this->schedulerStat(),
            $this->logStat(),
        ];
    }

    // ─── حالة الـ Queue ──────────────────────────────────────
    private function queueStat(): Stat
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $color       = $pendingJobs === 0 ? 'success' : ($pendingJobs < 10 ? 'warning' : 'danger');
            $description = $pendingJobs === 0 ? 'لا توجد مهام معلّقة' : "{$pendingJobs} مهمة في الانتظار";

            return Stat::make('Queue', $pendingJobs === 0 ? 'نظيف ✓' : $pendingJobs)
                ->description($description)
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($color);
        } catch (\Exception) {
            return Stat::make('Queue', 'غير متاح')
                ->description('تعذر الاتصال بجدول jobs')
                ->color('gray');
        }
    }

    // ─── Failed Jobs ─────────────────────────────────────────
    private function failedJobsStat(): Stat
    {
        try {
            $failed      = DB::table('failed_jobs')->count();
            $recentFailed = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHours(24))
                ->count();

            $color       = $failed === 0 ? 'success' : ($failed < 5 ? 'warning' : 'danger');
            $description = $recentFailed > 0
                ? "{$recentFailed} فشل في آخر 24 ساعة"
                : 'لا إخفاقات حديثة';

            return Stat::make('Failed Jobs', $failed)
                ->description($description)
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($color);
        } catch (\Exception) {
            return Stat::make('Failed Jobs', '—')
                ->description('جدول failed_jobs غير موجود')
                ->color('gray');
        }
    }

    // ─── Scheduler (آخر تشغيل) ───────────────────────────────
    private function schedulerStat(): Stat
    {
        // Laravel يكتب آخر تشغيل للـ scheduler في cache أو يمكن تتبعه عبر log
        $lastRun = Cache::get('scheduler:last_run');

        if ($lastRun) {
            $diffMins    = now()->diffInMinutes($lastRun);
            $color       = $diffMins <= 2 ? 'success' : ($diffMins <= 10 ? 'warning' : 'danger');
            $description = "آخر تشغيل: منذ {$diffMins} دقيقة";
            $value       = $diffMins <= 2 ? 'يعمل ✓' : "منذ {$diffMins}د";
        } else {
            // تحقق من log file لمعرفة آخر تشغيل
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $modifiedAt  = \Carbon\Carbon::createFromTimestamp(filemtime($logPath));
                $diffMins    = now()->diffInMinutes($modifiedAt);
                $color       = 'warning';
                $description = "آخر نشاط في الـ log: منذ {$diffMins} دقيقة";
                $value       = 'لم يُسجَّل';
            } else {
                $color       = 'gray';
                $description = 'لم يُنفَّذ بعد';
                $value       = 'غير معروف';
            }
        }

        return Stat::make('Scheduler', $value)
            ->description($description)
            ->descriptionIcon('heroicon-m-clock')
            ->color($color);
    }

    // ─── حجم ملف الـ Log ─────────────────────────────────────
    private function logStat(): Stat
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return Stat::make('Log File', 'فارغ')
                ->description('لا يوجد ملف log')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success');
        }

        $bytes = filesize($logPath);
        $size  = $this->formatBytes($bytes);
        $color = $bytes < 1_000_000 ? 'success'  // أقل من 1MB
               : ($bytes < 10_000_000 ? 'warning' // أقل من 10MB
               : 'danger');                         // 10MB+

        $lastModified = \Carbon\Carbon::createFromTimestamp(filemtime($logPath));

        return Stat::make('Log File', $size)
            ->description('آخر تعديل: ' . $lastModified->diffForHumans())
            ->descriptionIcon('heroicon-m-document-text')
            ->color($color);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024)       return $bytes . ' B';
        if ($bytes < 1_048_576)  return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1_073_741_824) return round($bytes / 1_048_576, 1) . ' MB';
        return round($bytes / 1_073_741_824, 1) . ' GB';
    }
}
