<?php

namespace App\Exceptions\Backup;

use RuntimeException;

/**
 * يُرمى عند فشل أي تحقق من سلامة الأرشيف قبل الاستخراج: checksum غير مطابق،
 * أو تعذّر فك التشفير (مفتاح غائب أو خاطئ).
 */
class BackupIntegrityException extends RuntimeException
{
}
