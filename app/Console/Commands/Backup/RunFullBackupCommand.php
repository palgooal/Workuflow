<?php

namespace App\Console\Commands\Backup;

use App\Jobs\Backup\RunSystemBackupJob;
use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Console\Command;

/**
 * backup:full — يُجدوَل أسبوعياً (routes/console.php). قاعدة بيانات + ملفات
 * storage الضرورية (راجع config('backups.system_backup.include_storage_paths')).
 */
class RunFullBackupCommand extends Command
{
    protected $signature = 'backup:full';

    protected $description = 'إنشاء نسخة احتياطية كاملة (قاعدة بيانات + ملفات ضرورية) — مجدولة أسبوعياً';

    public function handle(): int
    {
        $backup = Backup::create([
            'name'   => 'scheduled-full-'.now()->format('Ymd-His'),
            'type'   => BackupType::Full,
            'status' => BackupStatus::Pending,
        ]);

        RunSystemBackupJob::dispatch($backup->id);

        $this->info("تم إطلاق نسخة كاملة (Backup #{$backup->id}) على طابور backups (queue:work database --queue=...,backups).");

        return self::SUCCESS;
    }
}
