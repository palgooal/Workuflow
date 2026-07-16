<?php

namespace App\Console\Commands\Backup;

use App\Jobs\Backup\RunSystemBackupJob;
use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Console\Command;

/**
 * backup:database — يُجدوَل يومياً (routes/console.php). ينشئ سجل Backup
 * ويُطلِق RunSystemBackupJob على طابور "backups" (queue name — على نفس
 * connection الافتراضي database) — التنفيذ الفعلي (mysqldump + تشفير) يحدث
 * داخل الـ Job وليس هنا، حتى لا يحجب الأمر Console لمدة طويلة عند التشغيل
 * اليدوي وحتى يستفيد من retry/backoff الخاص بالـ Job.
 */
class RunDatabaseBackupCommand extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'إنشاء نسخة احتياطية لقاعدة البيانات فقط (مجدولة يومياً)';

    public function handle(): int
    {
        $backup = Backup::create([
            'name'   => 'scheduled-database-'.now()->format('Ymd-His'),
            'type'   => BackupType::Database,
            'status' => BackupStatus::Pending,
        ]);

        RunSystemBackupJob::dispatch($backup->id);

        $this->info("تم إطلاق نسخة قاعدة البيانات (Backup #{$backup->id}) على طابور backups (queue:work database --queue=...,backups).");

        return self::SUCCESS;
    }
}
