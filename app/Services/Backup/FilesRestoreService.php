<?php

namespace App\Services\Backup;

use App\Exceptions\Backup\BackupManifestException;
use App\Exceptions\Backup\BackupRestoreException;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * FilesRestoreService — مسؤولة عن الاستعادة الفعلية لملفات storage/app (المرحلة
 * الثالثة والأخيرة من Restore Engine).
 *
 * ⚠️ نطاق العمل محصور حصراً بـ storage/app (نفس ما تُنشئه SystemBackupService
 * فعلياً تحت بادئة "storage/" في الأرشيف — راجع include_storage_paths في
 * config/backups.php، وكلاهما app/private/... وapp/public تحت app/). لا يُلمَس
 * إطلاقاً storage/logs، storage/framework، bootstrap/cache، vendor، public،
 * أو أي مجلد آخر خارج storage/app.
 *
 * ⚠️ الاستبدال ذرّي قدر الإمكان (rename فقط، بدون shell، بدون shell_exec):
 *   storage/app → storage/app.__old   (نسخة الطوارئ: rename فوري، لا نسخ فعلي
 *                                       لمحتوى قد يكون ضخماً)
 *   [بناء storage/app.__restore من محتوى الأرشيف فقط — الملفات غير الموجودة في
 *    الأرشيف لا تُنسَخ إليه إطلاقاً، فتُحذَف تلقائياً من storage/app النهائية]
 *   storage/app.__restore → storage/app   (النقل النهائي، rename ذرّي)
 *   حذف storage/app.__old   (بعد النجاح فقط)
 * أي فشل بعد نجاح "نسخة الطوارئ" (سواء أثناء بناء .__restore أو أثناء النقل
 * النهائي) يُشغِّل rollback كاملاً: حذف .__restore الجزئي، وإعادة .__old إلى
 * موضعها الأصلي storage/app — بحيث لا يبقى storage/app أبداً في حالة مفقودة
 * أو جزئية.
 *
 * ⚠️ لا شيء يُنفَّذ إطلاقاً لنسخة من نوع "database" — return مبكر بدون أي لمس
 * للملفات.
 *
 * ⚠️ أمان: رفض أي رابط رمزي (symlink) داخل شجرة الأرشيف المستخرَجة قبل أي نسخ
 * (طبقة أولى: فحص كامل مسبق؛ طبقة ثانية: فحص أثناء النسخ نفسه). كل مسار يُنسَخ
 * يُتحقَّق بـrealpath() أنه فعلاً داخل مجلد المصدر (دفاع إضافي ضد أي شكل من
 * أشكال الهروب، رغم أن RestoreService::extractSafely() تمنع Zip Slip أصلاً
 * أثناء الاستخراج قبل الوصول لهذه الخدمة).
 *
 * ⚠️ قابلية الاختبار: عمليات rename/copy/delete معزولة في AtomicFilesystem
 * ومُحقَنة عبر المُنشئ — تسمح للاختبارات بمحاكاة فشل نقل محدَّد (Mock/Partial
 * Mock على methods عامة) دون الحاجة لـ shouldAllowMockingProtectedMethods()
 * ودون أي حيلة خاصة بنظام تشغيل معيّن.
 */
class FilesRestoreService
{
    public function __construct(
        private readonly AtomicFilesystem $files,
    ) {}

    /**
     * @throws BackupManifestException عند غياب storage/app داخل الأرشيف المستخرَج (لنسخة "كاملة")
     * @throws BackupRestoreException  عند أي فشل أثناء الاستعادة الفعلية (symlink مرفوض،
     *                                 فشل نسخة الطوارئ، فشل النقل النهائي بعد rollback كامل)
     */
    public function restore(string $extractDir, BackupType $type): void
    {
        if ($type !== BackupType::Full) {
            return;
        }

        $sourceDir = $extractDir.'/storage/app';

        if (! is_dir($sourceDir)) {
            throw new BackupManifestException("مجلد storage/app غير موجود داخل الأرشيف المستخرَج: {$sourceDir}");
        }

        $targetDir = $this->targetAppDir();
        $oldDir = $targetDir.'.__old';
        $restoreDir = $targetDir.'.__restore';

        $this->assertNoStaleTempDirs($oldDir, $restoreDir);
        $this->assertNoSymlinks($sourceDir);

        $this->createEmergencyBackup($targetDir, $oldDir);

        try {
            Log::info('Storage restore started');

            $this->buildRestoreStaging($sourceDir, $restoreDir);
            $this->atomicSwap($restoreDir, $targetDir);

            Log::info('Storage restore finished');
        } catch (Throwable $e) {
            $this->rollback($oldDir, $targetDir, $restoreDir);

            throw $e instanceof BackupRestoreException
                ? $e
                : new BackupRestoreException('فشلت استعادة ملفات storage/app: '.$e->getMessage());
        }

        $this->files->deleteDirectory($oldDir);
    }

    /**
     * مسار storage/app الفعلي المستهدَف — قابل للتوجيه عبر
     * backups.restore.storage_app_path في الاختبارات فقط (null = المسار
     * الحقيقي storage_path('app') دائماً في الإنتاج/التطوير).
     */
    private function targetAppDir(): string
    {
        return config('backups.restore.storage_app_path') ?: storage_path('app');
    }

    /**
     * يرفض المتابعة إذا وُجدت بقايا مجلدات مؤقتة من محاولة سابقة — أكثر أماناً
     * من حذفها تلقائياً (قد تكون آخر نسخة سليمة من storage/app إن حدث انقطاع
     * غير متوقَّع في منتصف تنفيذ سابق).
     */
    private function assertNoStaleTempDirs(string $oldDir, string $restoreDir): void
    {
        if (is_dir($oldDir) || is_dir($restoreDir)) {
            throw new BackupRestoreException(
                'يوجد مجلد مؤقت متبقٍّ من محاولة استعادة سابقة (storage/app.__old أو storage/app.__restore) — '.
                'يجب مراجعة الخادم يدوياً قبل إعادة المحاولة لتفادي فقدان بيانات.'
            );
        }
    }

    /**
     * فحص مسبق كامل لشجرة المصدر بالكامل — يرفض العملية فوراً وقبل لمس
     * storage/app الحقيقي إطلاقاً إذا وُجد أي رابط رمزي بداخلها.
     */
    private function assertNoSymlinks(string $sourceDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isLink()) {
                throw new BackupRestoreException(
                    'رُفِضت العملية: تم العثور على رابط رمزي (symlink) داخل نسخة storage/app المستخرَجة — غير مسموح به لأسباب أمنية.'
                );
            }
        }
    }

    /**
     * "نسخة الطوارئ" الفعلية: rename فوري وذرّي لـstorage/app الحالي إلى
     * storage/app.__old، بدل نسخ محتوى قد يكون ضخماً إلى أرشيف منفصل. فشل هذه
     * الخطوة يوقف العملية بالكامل قبل أي تعديل آخر (لا شيء تغيَّر بعد).
     */
    private function createEmergencyBackup(string $targetDir, string $oldDir): void
    {
        if (! is_dir($targetDir)) {
            // لا يوجد storage/app حالياً أصلاً — لا شيء لإخلائه جانباً
            return;
        }

        if (! $this->files->move($targetDir, $oldDir)) {
            throw new BackupRestoreException(
                'فشل إنشاء نسخة الطوارئ من storage/app قبل الاستعادة — تم إيقاف عملية استعادة الملفات بالكامل.'
            );
        }

        Log::info('Storage emergency backup created');
    }

    /**
     * ينسخ شجرة المصدر بالكامل إلى مجلد staging جديد ($restoreDir) — لا يُنشأ
     * أو يُعدَّل storage/app الحقيقي في هذه الخطوة إطلاقاً (staging منفصل تماماً).
     * الملفات غير الموجودة في المصدر لا تُنسَخ بالتبعية — هذا وحده يضمن أن
     * storage/app النهائية تطابق الأرشيف تماماً (لا حاجة لخطوة "حذف" منفصلة).
     */
    private function buildRestoreStaging(string $sourceDir, string $restoreDir): void
    {
        File::ensureDirectoryExists($restoreDir);

        $realSourceDir = realpath($sourceDir);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isLink()) {
                throw new BackupRestoreException('رُفِضت العملية: تم العثور على رابط رمزي (symlink) أثناء نسخ storage/app.');
            }

            $realItemPath = realpath($item->getPathname());
            if ($realItemPath === false || ! str_starts_with($realItemPath, $realSourceDir)) {
                throw new BackupRestoreException('رُفِضت العملية: مسار غير آمن أثناء نسخ storage/app.');
            }

            $relativePath = ltrim(str_replace($sourceDir, '', $item->getPathname()), '/\\');
            $destPath = $restoreDir.'/'.$relativePath;

            if ($item->isDir()) {
                File::ensureDirectoryExists($destPath);
                continue;
            }

            File::ensureDirectoryExists(dirname($destPath));

            if (! $this->files->copy($item->getPathname(), $destPath)) {
                throw new BackupRestoreException("تعذّر نسخ ملف أثناء بناء استعادة storage/app: {$relativePath}");
            }
        }
    }

    /**
     * النقل النهائي الذرّي: storage/app.__restore → storage/app (المسار
     * الحقيقي، الفارغ حالياً بعد نسخة الطوارئ أعلاه).
     */
    private function atomicSwap(string $restoreDir, string $targetDir): void
    {
        if (! $this->files->move($restoreDir, $targetDir)) {
            throw new BackupRestoreException('فشل النقل النهائي لملفات storage/app المستعادة إلى موضعها.');
        }
    }

    /**
     * Rollback كامل: حذف أي .__restore جزئي، ثم إعادة .__old إلى موضعه
     * الأصلي storage/app — بحيث لا يبقى storage/app أبداً مفقوداً أو جزئياً.
     */
    private function rollback(string $oldDir, string $targetDir, string $restoreDir): void
    {
        if (is_dir($restoreDir)) {
            $this->files->deleteDirectory($restoreDir);
        }

        if (is_dir($targetDir)) {
            $this->files->deleteDirectory($targetDir);
        }

        if (is_dir($oldDir)) {
            $this->files->move($oldDir, $targetDir);
        }

        Log::info('Storage rollback executed');
    }
}
