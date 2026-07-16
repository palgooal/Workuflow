<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * BackupEncryptor — تشفير/فك تشفير أرشيفات النسخ الاحتياطية التشغيلية.
 *
 * ⚠️ قواعد صارمة:
 *  - المفتاح يُقرأ فقط من متغيّر البيئة المحدَّد في config('backups.system_backup.encryption_key_env')
 *    (افتراضياً BACKUP_ENCRYPTION_KEY) — لا يُخزَّن أبداً داخل الأرشيف نفسه، ولا داخل
 *    manifest.json، ولا في أي ملف نصي منفصل يُحفَظ مع النسخة.
 *  - مفتاح النسخ الاحتياطي منفصل تماماً عن APP_KEY (لا نستخدم Illuminate\Support\Facades\Crypt
 *    حتى لا يرتبط أمن النسخ بدورة حياة APP_KEY، ولإمكانية تدوير أحدهما دون الآخر).
 *  - في حال غياب المفتاح: نرفض إنشاء نسخة غير مشفَّرة نهائياً (fail-safe).
 */
class BackupEncryptor
{
    private const CIPHER = 'aes-256-cbc';

    public function hasKey(): bool
    {
        return filled($this->rawKey());
    }

    /**
     * يشفّر ملفاً كاملاً ويكتب الناتج (IV + نص مشفّر) في $destinationPath.
     */
    public function encryptFile(string $sourcePath, string $destinationPath): void
    {
        $key = $this->resolvedKey();

        $plaintext = file_get_contents($sourcePath);
        if ($plaintext === false) {
            throw new RuntimeException("تعذّرت قراءة الملف المراد تشفيره: {$sourcePath}");
        }

        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));

        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new RuntimeException('فشل تشفير أرشيف النسخة الاحتياطية.');
        }

        // IV يُخزَّن بوضوح في مقدمة الملف (ليس سرّياً — لازم فقط لفك التشفير)
        file_put_contents($destinationPath, $iv.$ciphertext);
    }

    /**
     * يفك تشفير ملف مشفَّر بواسطة encryptFile() أعلاه.
     */
    public function decryptFile(string $sourcePath, string $destinationPath, ?string $key = null): void
    {
        $key ??= $this->resolvedKey();

        $raw = file_get_contents($sourcePath);
        if ($raw === false) {
            throw new RuntimeException("تعذّرت قراءة الملف المشفَّر: {$sourcePath}");
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv         = substr($raw, 0, $ivLength);
        $ciphertext = substr($raw, $ivLength);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            throw new RuntimeException('فشل فك تشفير الأرشيف — تأكد من صحة BACKUP_ENCRYPTION_KEY.');
        }

        file_put_contents($destinationPath, $plaintext);
    }

    public function checksum(string $path): string
    {
        return hash_file('sha256', $path);
    }

    private function resolvedKey(): string
    {
        $key = $this->rawKey();

        if (blank($key)) {
            throw new RuntimeException(
                'BACKUP_ENCRYPTION_KEY غير معرَّف في .env — لن يتم إنشاء نسخة احتياطية غير مشفَّرة. '.
                'راجع docs/BACKUP-SYSTEM.md لتوليد المفتاح.'
            );
        }

        return $key;
    }

    private function rawKey(): ?string
    {
        $key = config('backups.system_backup.encryption_key');

        return is_string($key) && $key !== '' ? $key : null;
    }
}
