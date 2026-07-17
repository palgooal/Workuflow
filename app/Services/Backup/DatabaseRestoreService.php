<?php

namespace App\Services\Backup;

use App\Exceptions\Backup\BackupRestoreException;
use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * DatabaseRestoreService — مسؤولة عن الاستعادة الفعلية لقاعدة البيانات (المرحلة
 * الثانية من Restore Engine).
 *
 * ⚠️ لا علاقة لها باستعادة الملفات (storage/) — تلك تبقى تحقق فقط عبر
 * FilesRestoreService::validate() ولم تُعدَّل في هذه المرحلة.
 *
 * تُستدعى مرة واحدة فقط من RestoreService::run()، بعد أن يكون الأخير قد تحقّق
 * بنجاح كامل من: وجود السجل، وجود الملف، checksum، فك التشفير، فتح ZIP،
 * قراءة manifest.json، ومطابقة نوع/بنية الأرشيف — أي أن restore() هنا لا
 * تُستدعى أبداً إذا فشل أي من هذه التحققات (checksum/manifest/verify) — هذا
 * يضمن عدم تنفيذ أي SQL في حالة فشل أي بوابة تحقق سابقة.
 *
 * ترتيب restore(): تحقق من database.sql → تحقق من driver (mysql فقط، عبر اتصال
 * الاستعادة المنفصل — راجع resolveMysqlConnection()) → تحقق من توفر mysql CLI
 * → إنشاء نسخة احتياطية طارئة (تتوقف العملية بالكامل إن فشلت) → تفعيل
 * Maintenance Mode → تنفيذ الاستيراد الفعلي (بدون تمرير كلمة المرور في سطر
 * الأوامر) → عند النجاح: إعادة تشغيل cache/config/routes → إلغاء Maintenance
 * Mode داخل finally دائماً، بغض النظر عن النتيجة.
 *
 * ⚠️ قابلية الاختبار: كل اعتمادية ذات أثر جانبي حقيقي مُحقَنة عبر المُنشئ
 * (SystemBackupService، DatabaseRestoreProcessRunner) بدل استدعاء مباشر —
 * تسمح للاختبارات باستبدالها بالكامل (Mockery mock) دون تشغيل mysql CLI
 * حقيقي أو إنشاء نسخة احتياطية حقيقية. Maintenance Mode يُستدعى عبر واجهة
 * Artisan (Facade قابلة للمحاكاة أصلاً في Laravel دون أي غلاف إضافي).
 */
class DatabaseRestoreService
{
    public function __construct(
        private readonly SystemBackupService $backupService,
        private readonly DatabaseRestoreProcessRunner $processRunner,
    ) {}

    /**
     * ينفّذ الاستعادة الفعلية لقاعدة البيانات من ملف database.sql مستخرَج فعلياً
     * داخل مجلد عمل RestoreService المؤقت (restore-temp) — لا تُستدعى أبداً على
     * مسار خارج ذلك المجلد.
     *
     * @throws BackupRestoreException عند فشل أي خطوة (ملف غير صالح، driver غير
     *                                 مدعوم، mysql CLI غير متوفر، فشل النسخة
     *                                 الطارئة، أو فشل الاستيراد نفسه)
     */
    public function restore(string $sqlPath): void
    {
        $this->assertSqlFileValid($sqlPath);

        $connection = $this->resolveMysqlConnection();

        $this->assertMysqlCliAvailable();

        $this->createEmergencyBackup();

        $this->enterMaintenanceMode();

        try {
            $this->runImport($connection, $sqlPath);
            $this->refreshApplicationState();
        } finally {
            $this->exitMaintenanceMode();
        }
    }

    private function assertSqlFileValid(string $sqlPath): void
    {
        if (! is_file($sqlPath) || ! is_readable($sqlPath)) {
            throw new BackupRestoreException("database.sql غير موجود أو غير قابل للقراءة: {$sqlPath}");
        }

        if (filesize($sqlPath) <= 0) {
            throw new BackupRestoreException('database.sql موجود لكنه فارغ (حجمه صفر بايت).');
        }
    }

    /**
     * ⚠️ اسم اتصال الاستعادة يأتي من backups.restore.connection إن كان
     * محدَّداً، وإلا يُستخدَم database.default كما هو (هذا هو سلوك الإنتاج/
     * التطوير الافتراضي بدون أي متغيّر بيئة إضافي). هذا يسمح بفصل اتصال
     * الاستعادة عن اتصال اختبارات Laravel (sqlite :memory:) في الاختبارات
     * فقط، دون أي تأثير على أي كود آخر في التطبيق.
     *
     * ⚠️ هذا الاتصال يُستخدَم فقط كمصدر بيانات (host/port/username/password/
     * database) لبناء أمر Process لاحقاً — لا يُفتَح عبره أي اتصال PDO فعلي.
     *
     * @return array<string,mixed>
     */
    private function resolveMysqlConnection(): array
    {
        $connectionName = config('backups.restore.connection') ?: config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'] ?? null;

        if ($driver !== 'mysql') {
            throw new BackupRestoreException(
                'محرك استعادة قاعدة البيانات يدعم mysql فقط حالياً. '.
                'اتصال الاستعادة الحالي ("'.$connectionName.'") من نوع driver="'.($driver ?? 'غير محدَّد').'" غير مدعوم.'
            );
        }

        return $connection;
    }

    private function assertMysqlCliAvailable(): void
    {
        try {
            $result = $this->processRunner->checkMysqlCliAvailable();
        } catch (Throwable $e) {
            throw new BackupRestoreException('برنامج mysql CLI غير متوفر على الخادم: '.$e->getMessage());
        }

        if (! $result->successful()) {
            throw new BackupRestoreException(
                'برنامج mysql CLI غير متوفر أو تعذّر تشغيله على الخادم (exit code: '.$result->exitCode().').'
            );
        }
    }

    /**
     * إنشاء نسخة احتياطية طارئة لقاعدة البيانات الحالية قبل أي استيراد —
     * باستخدام SystemBackupService الحالية دون أي تعديل عليها. فشل هذه
     * الخطوة يوقف العملية بالكامل قبل الوصول لأي Maintenance Mode أو استيراد.
     */
    private function createEmergencyBackup(): void
    {
        $emergencyBackup = Backup::create([
            'name'   => 'pre-restore-emergency-'.now()->format('Ymd-His'),
            'type'   => BackupType::Database,
            'status' => BackupStatus::Pending,
        ]);

        try {
            $this->backupService->run($emergencyBackup);
        } catch (Throwable $e) {
            throw new BackupRestoreException(
                'فشل إنشاء النسخة الاحتياطية الطارئة قبل الاستعادة — تم إيقاف عملية الاستعادة بالكامل: '.$e->getMessage()
            );
        }

        if ($emergencyBackup->fresh()->status !== BackupStatus::Completed) {
            throw new BackupRestoreException(
                'فشلت النسخة الاحتياطية الطارئة قبل الاستعادة — تم إيقاف عملية الاستعادة بالكامل.'
            );
        }

        Log::info('Emergency backup created', ['backup_id' => $emergencyBackup->id]);
    }

    private function enterMaintenanceMode(): void
    {
        Artisan::call('down');
        Log::info('Maintenance mode enabled');
    }

    private function exitMaintenanceMode(): void
    {
        Artisan::call('up');
        Log::info('Maintenance mode disabled');
    }

    /** @param array<string,mixed> $connection */
    private function runImport(array $connection, string $sqlPath): void
    {
        Log::info('Database restore started');

        $result = $this->processRunner->runImport($connection, $sqlPath);

        if (! $result->successful()) {
            throw new BackupRestoreException(
                'فشل استيراد قاعدة البيانات (exit code: '.$result->exitCode().'): '.$result->errorOutput()
            );
        }

        Log::info('Database restore finished');
    }

    private function refreshApplicationState(): void
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
    }
}
