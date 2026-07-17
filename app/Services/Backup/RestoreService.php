<?php

namespace App\Services\Backup;

use App\Exceptions\Backup\BackupIntegrityException;
use App\Exceptions\Backup\BackupManifestException;
use App\Exceptions\Backup\BackupNotFoundException;
use App\Exceptions\Backup\BackupRestoreException;
use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * RestoreService — منسِّق (Orchestrator) محرك الاستعادة.
 *
 * ⚠️ هذه الفئة **لا** تنفّذ استيراد SQL ولا نسخ ملفات بنفسها مباشرة — التنفيذ
 * دائماً مفوَّض لـ DatabaseRestoreService وFilesRestoreService عبر استدعاء
 * واحد فقط لكل منهما. RestoreService هي المالكة الوحيدة لدورة حياة العملية
 * (فك تشفير واستخراج مرة واحدة فقط — لا ازدواجية مع أي منطق آخر).
 *
 * ⚠️ المرحلتان الثانية والثالثة (مكتملتان الآن): DatabaseRestoreService::restore()
 * تنفّذ استعادة حقيقية فعلية لقاعدة البيانات (نسخة طارئة + Maintenance Mode +
 * استيراد mysql فعلي)، وFilesRestoreService::restore() تنفّذ استعادة حقيقية
 * فعلية لملفات storage/app (استبدال ذرّي + rollback كامل عند الفشل) — تُستدعى
 * فقط بعد نجاح استعادة قاعدة البيانات، وفقط لنسخة من نوع "كاملة" (لا تفعل شيئاً
 * لنسخة "قاعدة بيانات فقط"). محرك الاستعادة أصبح مكتملاً وظيفياً بهذا.
 *
 * ⚠️ RestoreBackupCommand (backup:restore) يستدعي run() هذه مرة واحدة فقط،
 * بعد الحصول على تأكيد المستخدم الصريح — لا يوجد أي فك تشفير أو استخراج أو
 * استيراد مكرَّر خارج هذه الفئة.
 *
 * ⚠️ أمان: الاستخراج يتم عبر extractSafely() فقط (وليس ZipArchive::extractTo())
 * — يرفض أي مسار داخل الأرشيف يحاول الخروج عن مجلد العمل المؤقت (Zip Slip /
 * Path Traversal)، ويتحقق بـ realpath() بعد الكتابة أيضاً كطبقة حماية ثانية.
 * لا يُستدعى DatabaseRestoreService::restore() أبداً إلا بعد نجاح كامل لكل
 * بوابات التحقق أعلاه (checksum/فك تشفير/manifest/بنية الأرشيف) — أي فشل في
 * أي منها يوقف العملية قبل تنفيذ أي SQL.
 */
class RestoreService
{
    public function __construct(
        private readonly BackupEncryptor $encryptor,
        private readonly DatabaseRestoreService $databaseRestoreService,
        private readonly FilesRestoreService $filesRestoreService,
    ) {}

    /**
     * ينفّذ خط أنابيب تحقق الاستعادة الكامل (بدون أي تنفيذ فعلي بعد) وفق
     * الترتيب: وجود السجل → وجود الملف → checksum → فك تشفير → فتح ZIP →
     * قراءة manifest → التحقق من النوع/البنية → استخراج آمن → تفويض التحقق
     * لـ DatabaseRestoreService/FilesRestoreService → تنظيف كامل في finally.
     *
     * @return array{ok: bool, backup_id: string, manifest: array<string,mixed>, restore_id: string}
     *
     * @throws BackupNotFoundException|BackupIntegrityException|BackupManifestException|BackupRestoreException
     */
    public function run(string $backupId): array
    {
        Log::info('Restore started', ['backup_id' => $backupId]);

        // 1) التحقق من وجود سجل Backup
        /** @var Backup|null $backup */
        $backup = Backup::query()->find($backupId);

        if (! $backup) {
            throw new BackupNotFoundException("لا توجد نسخة احتياطية بالمعرّف: {$backupId}");
        }

        if ($backup->status !== BackupStatus::Completed) {
            throw new BackupNotFoundException('لا يمكن استعادة نسخة غير مكتملة (status='.$backup->status->value.').');
        }

        // 2) التحقق من وجود الملف
        if (! $backup->disk || ! $backup->path || ! Storage::disk($backup->disk)->exists($backup->path)) {
            throw new BackupIntegrityException('ملف النسخة غير موجود على القرص.');
        }

        $restoreId = (string) Str::uuid();
        $workDir = storage_path("app/backups/restore-temp/{$restoreId}");
        $encPath = $workDir.'/archive.zip.enc';
        $zipPath = $workDir.'/archive.zip';
        $extractDir = $workDir.'/extracted';

        try {
            File::ensureDirectoryExists($workDir);
            File::ensureDirectoryExists($extractDir);

            File::put($encPath, Storage::disk($backup->disk)->get($backup->path));

            // 3) التحقق من SHA256 — توقف كامل عند الفشل
            $actualChecksum = hash_file('sha256', $encPath);
            if (! $backup->checksum || $actualChecksum !== $backup->checksum) {
                throw new BackupIntegrityException('checksum غير مطابق — الأرشيف قد يكون تالفاً أو مُعدَّلاً.');
            }
            Log::info('Checksum verified', ['backup_id' => $backupId]);

            // 4) فك التشفير (باستخدام BackupEncryptor الحالية دون أي تعديل)
            if (! $this->encryptor->hasKey()) {
                throw new BackupIntegrityException('BACKUP_ENCRYPTION_KEY غير معرَّف — لا يمكن فك التشفير.');
            }

            try {
                $this->encryptor->decryptFile($encPath, $zipPath);
            } catch (Throwable $e) {
                throw new BackupIntegrityException('فشل فك التشفير: '.$e->getMessage());
            }
            Log::info('Archive decrypted', ['backup_id' => $backupId]);

            // 5) فتح ZIP
            $zip = new ZipArchive();
            $openResult = $zip->open($zipPath);
            if ($openResult !== true) {
                throw new BackupManifestException('تعذّر فتح الأرشيف كملف ZIP صالح (كود الخطأ: '.$openResult.').');
            }

            try {
                // 6) قراءة manifest.json
                $manifestJson = $zip->getFromName('manifest.json');
                if ($manifestJson === false) {
                    throw new BackupManifestException('manifest.json غير موجود داخل الأرشيف.');
                }

                $manifest = json_decode($manifestJson, true);
                if (! is_array($manifest)) {
                    throw new BackupManifestException('manifest.json موجود لكن محتواه غير صالح (JSON غير سليم).');
                }
                Log::info('Manifest loaded', ['backup_id' => $backupId]);

                // 7) التحقق من نوع النسخة + سلامة manifest + وجود database.sql (+ storage/ للكاملة)
                if (($manifest['type'] ?? null) !== $backup->type->value) {
                    throw new BackupManifestException(
                        'نوع النسخة داخل manifest.json ("'.($manifest['type'] ?? 'غير محدَّد').'") لا يطابق نوع السجل المسجَّل ("'.$backup->type->value.'").'
                    );
                }

                if ($zip->locateName('database.sql') === false) {
                    throw new BackupManifestException('database.sql غير موجود داخل الأرشيف.');
                }

                if ($backup->type === BackupType::Full && ! $this->zipHasEntriesUnder($zip, 'storage/')) {
                    throw new BackupManifestException('مجلد storage/ غير موجود داخل الأرشيف رغم أن نوع النسخة "كاملة".');
                }

                // 8-9) مجلد العمل المؤقت مُنشَأ أعلاه بالفعل — استخراج آمن الآن
                $this->extractSafely($zip, $extractDir);
            } finally {
                $zip->close();
            }

            // 10) تفويض الاستعادة الفعلية لقاعدة البيانات — استدعاء واحد فقط
            // (DatabaseRestoreService تسجّل بنفسها مراحلها الخاصة: نسخة طارئة،
            // Maintenance Mode، بدء/انتهاء الاستيراد — لا نكرر تسجيلاً هنا)
            $this->databaseRestoreService->restore($extractDir.'/database.sql');

            // 11) تفويض الاستعادة الفعلية للملفات — استدعاء واحد فقط، بعد نجاح
            // استعادة قاعدة البيانات مباشرة. لا شيء يحدث هنا لنسخة "قاعدة بيانات
            // فقط" (FilesRestoreService تُرجع فوراً بدون أي لمس للملفات).
            // (FilesRestoreService تسجّل مراحلها الخاصة بنفسها — لا نكرر تسجيلاً هنا)
            $this->filesRestoreService->restore($extractDir, $backup->type);

            Log::info('Restore finished', ['backup_id' => $backupId]);

            return [
                'ok'         => true,
                'backup_id'  => $backup->id,
                'manifest'   => $manifest,
                'restore_id' => $restoreId,
            ];
        } finally {
            // 12) تنظيف كامل — مهما كانت النتيجة (نجاح أو أي نوع استثناء)
            File::deleteDirectory($workDir);
        }
    }

    private function zipHasEntriesUnder(ZipArchive $zip, string $prefix): bool
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name !== false && str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * يستخرج كل ملف من ZIP يدوياً (عبر streams، بلا تحميل كامل في الذاكرة)
     * بعد التحقق الصارم من مساره — يمنع Zip Slip / Path Traversal كلياً، بدل
     * الاعتماد فقط على ZipArchive::extractTo() (التي لا تحمي من هذا بمفردها
     * في كل الحالات/الإصدارات).
     *
     * طبقتا حماية: (أ) رفض أي اسم مدخل يحتوي على ".."، يبدأ بـ "/"، أو يشبه
     * مسار Windows مطلقاً (C:\...)، قبل أي كتابة. (ب) بعد الكتابة، realpath()
     * الفعلي للملف يجب أن يبقى بادئته extractDir الحقيقي — يكتشف أيضاً حيل
     * الروابط الرمزية (symlink)، وليس فقط أسماء المسارات النصية.
     *
     * @throws BackupRestoreException عند أي مسار غير آمن أو فشل استخراج
     */
    private function extractSafely(ZipArchive $zip, string $extractDir): void
    {
        $realExtractDir = realpath($extractDir);
        if ($realExtractDir === false) {
            throw new BackupRestoreException('تعذّر تحديد مسار مجلد الاستخراج الحقيقي.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }

            if (
                str_contains($name, '..')
                || str_starts_with($name, '/')
                || str_starts_with($name, '\\')
                || preg_match('#^[a-zA-Z]:[\\\\/]#', $name) === 1
            ) {
                throw new BackupRestoreException("رُفِضت العملية: مسار غير آمن داخل الأرشيف (\"{$name}\") — احتمال Zip Slip.");
            }

            $targetPath = $extractDir.'/'.$name;

            if (str_ends_with($name, '/')) {
                File::ensureDirectoryExists($targetPath);
                continue;
            }

            File::ensureDirectoryExists(dirname($targetPath));

            $stream = $zip->getStream($name);
            if ($stream === false) {
                throw new BackupRestoreException("تعذّرت قراءة الملف داخل الأرشيف: {$name}");
            }

            $out = fopen($targetPath, 'wb');
            if ($out === false) {
                fclose($stream);
                throw new BackupRestoreException("تعذّرت الكتابة إلى: {$targetPath}");
            }

            stream_copy_to_stream($stream, $out);
            fclose($out);
            fclose($stream);

            $realTarget = realpath($targetPath);
            if ($realTarget === false || ! str_starts_with($realTarget, $realExtractDir)) {
                throw new BackupRestoreException("رُفِضت العملية: الملف المستخرَج خرج فعلياً عن مجلد العمل المؤقت (\"{$name}\").");
            }
        }
    }
}
