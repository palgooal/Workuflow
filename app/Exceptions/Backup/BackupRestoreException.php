<?php

namespace App\Exceptions\Backup;

use RuntimeException;

/**
 * استثناء عام لمراحل الاستعادة التي لا تندرج تحت الفئات الأكثر تحديداً أعلاه
 * (BackupNotFoundException / BackupIntegrityException / BackupManifestException)
 * — مثل تعذّر فتح الأرشيف كـ ZIP، فشل الاستخراج، أو رفض مسار غير آمن (Zip Slip).
 */
class BackupRestoreException extends RuntimeException
{
}
