<?php

namespace App\Services\Backup;

use App\Models\Backup;
use App\Support\Enums\BackupStatus;
use App\Support\Enums\BackupType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * BackupInspectionService — قراءة تفاصيل نسخة احتياطية (manifest.json) وفحص
 * سلامتها الكامل، للاستخدام في صفحة "تفاصيل النسخة" في Filament فقط.
 *
 * ⚠️ لا علاقة له بإنشاء/استعادة النسخ — لا يستخدم إلا SystemBackupService/
 * BackupEncryptor الحاليَين للقراءة فقط (فك تشفير مؤقت)، ولا يعدّل أياً منهما.
 *
 * ⚠️ قواعد صارمة (أمان):
 *  - لا يُترَك أي ملف مفكوك/مفكوك التشفير على القرص بعد انتهاء أي دالة هنا —
 *    كل الملفات المؤقتة تُحذَف داخل finally، حتى عند حدوث استثناء.
 *  - لا يُحفَظ manifest.json ولا database.sql ولا أي محتوى مستخرَج بشكل دائم —
 *    كل قراءة تُعيد فك التشفير والاستخراج من جديد (مقصود، وليس Cache).
 *  - verify() يُحدِّث integrity_verified/integrity_checked_at فقط عند النجاح
 *    الكامل. عند أي فشل، لا يُغيَّر أي عمود في سجل Backup إطلاقاً.
 */
class BackupInspectionService
{
    public function __construct(
        private readonly BackupEncryptor $encryptor,
    ) {}

    /**
     * يقرأ manifest.json من نسخة مكتملة + يحسب عدد الملفات والحجم الكلي
     * الفعلي داخل الأرشيف (غير المضغوط)، عبر فك تشفير مؤقت فقط.
     *
     * @return array{manifest: array<string,mixed>, file_count: int, total_size: int}
     *
     * @throws RuntimeException عند تعذّر القراءة لأي سبب (رسالة واضحة قابلة للعرض)
     */
    public function readManifest(Backup $backup): array
    {
        if ($backup->status !== BackupStatus::Completed) {
            throw new RuntimeException('لا يمكن قراءة تفاصيل النسخة قبل اكتمالها.');
        }

        if (! $backup->disk || ! $backup->path || ! Storage::disk($backup->disk)->exists($backup->path)) {
            throw new RuntimeException('ملف النسخة غير موجود على القرص.');
        }

        if (! $this->encryptor->hasKey()) {
            throw new RuntimeException('BACKUP_ENCRYPTION_KEY غير معرَّف — لا يمكن فك التشفير لعرض التفاصيل.');
        }

        $tmpEnc = storage_path('app/private/tmp/inspect-'.Str::ulid().'.enc');
        $tmpZip = storage_path('app/private/tmp/inspect-'.Str::ulid().'.zip');

        try {
            File::ensureDirectoryExists(dirname($tmpEnc));
            File::put($tmpEnc, Storage::disk($backup->disk)->get($backup->path));

            $this->encryptor->decryptFile($tmpEnc, $tmpZip);

            $zip = new ZipArchive();
            if ($zip->open($tmpZip) !== true) {
                throw new RuntimeException('تعذّر فتح الأرشيف كملف ZIP صالح.');
            }

            try {
                $manifestJson = $zip->getFromName('manifest.json');
                if ($manifestJson === false) {
                    throw new RuntimeException('manifest.json غير موجود داخل الأرشيف.');
                }

                $manifest = json_decode($manifestJson, true);
                if (! is_array($manifest)) {
                    throw new RuntimeException('manifest.json موجود لكن محتواه غير صالح.');
                }

                $fileCount = 0;
                $totalSize = 0;

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if ($stat === false || str_ends_with($stat['name'], '/')) {
                        continue; // تجاهل مدخلات "المجلدات" داخل ZIP
                    }
                    $fileCount++;
                    $totalSize += (int) $stat['size'];
                }

                return [
                    'manifest'   => $manifest,
                    'file_count' => $fileCount,
                    'total_size' => $totalSize,
                ];
            } finally {
                $zip->close();
            }
        } finally {
            File::delete($tmpEnc);
            File::delete($tmpZip);
        }
    }

    /**
     * فحص سلامة كامل (8 خطوات): وجود الملف، checksum، فك تشفير مؤقت، صلاحية
     * ZIP، وجود manifest.json، ووجود database.sql (+ storage/ للنسخة الكاملة).
     *
     * عند النجاح الكامل فقط: يُحدِّث integrity_verified=true وintegrity_checked_at.
     * عند أي فشل: لا يُغيَّر أي عمود في السجل، ويعاد السبب في reason فقط.
     *
     * @return array{ok: bool, reason: ?string}
     */
    public function verify(Backup $backup): array
    {
        if (! $backup->disk || ! $backup->path) {
            return $this->failure('لا يوجد مسار تخزين مسجَّل لهذه النسخة.');
        }

        // 1) وجود الملف
        if (! Storage::disk($backup->disk)->exists($backup->path)) {
            return $this->failure('الملف غير موجود على القرص.');
        }

        $tmpEnc = storage_path('app/private/tmp/verify-detail-'.Str::ulid().'.enc');
        $tmpZip = storage_path('app/private/tmp/verify-detail-'.Str::ulid().'.zip');

        try {
            File::ensureDirectoryExists(dirname($tmpEnc));
            File::put($tmpEnc, Storage::disk($backup->disk)->get($backup->path));

            // 2) التحقق من SHA256 checksum
            $actualChecksum = hash_file('sha256', $tmpEnc);
            if (! $backup->checksum || $actualChecksum !== $backup->checksum) {
                return $this->failure('checksum غير مطابق — الأرشيف قد يكون تالفاً أو مُعدَّلاً.');
            }

            // 3) فك التشفير مؤقتاً
            if (! $this->encryptor->hasKey()) {
                return $this->failure('BACKUP_ENCRYPTION_KEY غير معرَّف — لا يمكن فك التشفير للتحقق.');
            }

            try {
                $this->encryptor->decryptFile($tmpEnc, $tmpZip);
            } catch (Throwable $e) {
                return $this->failure('فشل فك التشفير: '.$e->getMessage());
            }

            // 4) التأكد من إمكانية فتح ZIP
            $zip = new ZipArchive();
            $openResult = $zip->open($tmpZip);
            if ($openResult !== true) {
                return $this->failure('تعذّر فتح الأرشيف كملف ZIP صالح (كود الخطأ: '.$openResult.').');
            }

            try {
                // 5) وجود manifest.json
                if ($zip->locateName('manifest.json') === false) {
                    return $this->failure('manifest.json غير موجود داخل الأرشيف.');
                }

                // 6/7) وجود database.sql (+ storage/ للنسخة الكاملة)
                if ($zip->locateName('database.sql') === false) {
                    return $this->failure('database.sql غير موجود داخل الأرشيف.');
                }

                if ($backup->type === BackupType::Full && ! $this->zipHasEntriesUnder($zip, 'storage/')) {
                    return $this->failure('مجلد storage/ غير موجود داخل الأرشيف رغم أن نوع النسخة "كاملة".');
                }
            } finally {
                $zip->close();
            }

            // 8) نجاح كامل — تحديث integrity_verified/integrity_checked_at فقط
            $backup->update([
                'integrity_verified'   => true,
                'integrity_checked_at' => now(),
            ]);

            return ['ok' => true, 'reason' => null];
        } finally {
            File::delete($tmpEnc);
            File::delete($tmpZip);
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

    /** @return array{ok: bool, reason: ?string} */
    private function failure(string $reason): array
    {
        return ['ok' => false, 'reason' => $reason];
    }
}
